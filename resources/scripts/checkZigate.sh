#! /bin/bash

###
### Check TTY port and proper access to Zigate.
### WARNING: This script expects that daemon is stoppped to
###          not disturb communication with Zigate but option '-k' allows to kill using process if any.
###

# Usage
# checkZigate.sh [options] <zgPort> <zgType> [<gpioLib>]
# Possible options
# -k => kill process using port
# ex: checkZigate.sh /dev/ttyUSB0 USB
# ex: checkZigate.sh /dev/ttyAMA0 Piv2 PiGpio

# Display process ($1=Process ID)
displayProcess() {
    local PID=$1

    PSOUT=`ps --pid ${PID} -o ppid,cmd | grep -v PPID`
    IFS=' '
    read -ra PSOUTA <<< "${PSOUT}"
    PPID2=${PSOUTA[0]}
    CMD=${PSOUTA[@]:1}
    echo "= Process ${PID} details:"
    echo "=   PPid=${PPID2}, cmd='${CMD}'"
}

# Check if port is free
# $1=port
checkPort() {
    local PORT=$1
    PORTISFREE=0

    local FIELDS=`sudo lsof -Fcn ${PORT}`
    if [ "${FIELDS}" == "" ]; then
        echo "- Port seems free"
        PORTISFREE=1
        return
    fi

    # Port is used
    PID=0
    for f in ${FIELDS};
    do
        if [[ "$f" == "p"* ]]; then
            PID=${f:1}
            break
        fi
    done

    # Kill requested ?
    if [ ${KILLIFUSED} -eq 1 ]; then
        echo "- Port is used by process ${PID} (see below)"
        displayProcess ${PID}
        echo "- Killing process ${PID}"
        sudo kill -9 ${PID}
        # A small wait loop
        for (( T=1; T<=3; T++ ))
        do
            ps -p ${PID} >/dev/null 2>&1
            RES=$?
            # echo "res=${RES}"
            if [ ${RES} -ne 0 ]; then
                break
            fi
            sleep 1
        done
        ps -p ${PID} >/dev/null 2>&1
        if [ $? -eq 0 ]; then
            echo "= Can't kill process ${PID} using port ${PORT}."
            return
        fi
        PORTISFREE=1
    else
        echo "= ERROR: Port is used by process ${PID}."
        echo "=        You can add '-k' option to kill it and perform more tests."
        PORTISFREE=0

        echo
        displayProcess ${PID}

        echo
        echo "= Additional infos I (dmesg | grep tty):"
        dmesg | grep tty

        echo
        echo "= Additional infos II (ls -al /dev/serial*):"
        ls -al /dev/serial* 2>/dev/null

        if [ "$TYPE" == "USB" ] || [ "$TYPE" == "USBv2" ]; then
            echo
            echo "= Additional infos III (USB case):"
            dmesg | grep -i zigate
        fi
    fi
}

# Checking arguments
PORT=""
TYPE=""
GPIOLIB=""
KILLIFUSED=0
while [[ $# -gt 0 ]]; do
    if [[ "$1" == "-"* ]]; then
        case $1 in
            '-k')
                KILLIFUSED=1
                ;;
            *)
                echo "ERROR: Unexpected option '$1'"
                exit 1
                ;;
        esac
    else
        if [ "${PORT}" == "" ]; then
            PORT=$1
        elif [ "${TYPE}" == "" ]; then
            TYPE=$1
        elif [ "${GPIOLIB}" == "" ]; then
            GPIOLIB=$1
        else
            echo "ERROR: Unexpected arg '$1'"
            exit 1
        fi
    fi
    shift
done
if [ "${PORT}" == "" ]; then
    echo "ERROR: Missing port name (ex: /dev/ttyUSB0)"
    exit 1
fi
if [ "${TYPE}" == "" ]; then
    echo "ERROR: Missing Zigate type (PI, PIv2 or USB)"
    exit 1
fi
if [ "${TYPE}" != "PI" ] && [ "${TYPE}" != "PIv2" ] && [ "${TYPE}" != "USB" ]; then
    echo "ERROR: Invalid Zigate type (PI, PIv2 or USB)"
    exit 1
fi
if [ "${TYPE}" == "PI" ] || [ "${TYPE}" == "PIv2" ]; then
    if [ "${GPIOLIB}" == "" ]; then
        echo "ERROR: Missing GPIO lib (PiGpio or WiringPi)"
        exit 1
    elif [ "${GPIOLIB}" != "PiGpio" ] && [ "${GPIOLIB}" != "WiringPi" ]; then
        echo "ERROR: Invalid GPIO lib (PiGpio or WiringPi)"
        exit 1
    fi
fi

# Let's start
echo "Checking Zigate type ${TYPE} access on port ${PORT}"

# Global variables
PORTISFREE=0


# Port exists ?
if [ ! -e ${PORT} ]; then
    echo "= ERROR: Port ${PORT} does not exist !"
    exit 2
fi
echo "- ${PORT} port found"

command -v lsof >/dev/null
if [ $? -ne 0 ]; then
    echo "= ERROR: Could not find 'lsof' command"
    exit 3
fi

# Is port in use ?
checkPort ${PORT}
if [ "${PORTISFREE}" -ne 1 ]; then
    exit 4
fi
# FIELDS=`sudo lsof -Fcn ${PORT}`
# if [ "${FIELDS}" == "" ]; then
#     echo "- Port seems free"
# else
#     PID=0
#     for f in ${FIELDS};
#     do
#         if [[ "$f" == "p"* ]]; then
#             PID=${f:1}
#             break
#         fi
#     done

#     # Kill requested ?
#     if [ ${KILLIFUSED} -eq 1 ]; then
#         echo "- Port is used by process ${PID} => killing process ${PID}"
#         displayProcess ${PID}
#         kill -9 ${PID}
#         # TODO: While loop with timeout to wait for effective kill
#     else
#         echo "= ERROR: Port is used by process '${PID}'."
#         echo "=        You can add '-k' option to further tests anyway."

#         displayProcess ${PID}

#         echo
#         echo "= Additional infos I:"
#         dmesg | grep tty

#         echo
#         echo "= Additional infos II:"
#         ls -al /dev/serial*

#         exit 4
#     fi
# fi

# Port is free, let's interrogate Zigate but python is required for that.
command -v python3 >/dev/null
if [ $? -ne 0 ]; then
    echo "= ERROR: Could not find 'python3' command."
    echo "         It is required for next steps"
    exit 5
fi

if [ "${TYPE}" == "PI" ] || [ "${TYPE}" == "PIv2" ]; then
    echo "Configuring PI Zigate for 'prod' mode (lib=${GPIOLIB})"
    python3 core/python/AbeilleZigate.py zgSetPiMode prod ${GPIOLIB}
    if [ $? -ne 0 ]; then
        exit 6
    fi
fi

echo "Reading FW version"
python3 core/python/AbeilleZigate.py readFwVersion ${PORT}
STATUS=$?
if [ "${STATUS}" -ne 0 ]; then
    echo "- ERROR: Can't read FW version."
    # Check if port is free and kill if required
    checkPort ${PORT}
    if [ "${PORTISFREE}" -ne 1 ]; then
        echo "= ERROR: Port is already in use"
    fi
    echo "Retrying reading FW version"
    python3 core/python/AbeilleZigate.py readFwVersion ${PORT}
fi

exit 0
