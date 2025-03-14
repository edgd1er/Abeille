<!-- This file displays equipment commands.
     Included by 'Abeille-Eq-Advanced.php' -->

<?php
    if (isset($dbgDeveloperMode)) echo __FILE__;
?>

    <!-- telecommande: visible / hidden -->
    <div class="form-group" id="telecommande" style="display: none">

        <label>{{Télécommande}}</label></br>

        <!-- <div class="form-group">
            <label class="col-sm-3 control-label">{{Adresse groupe}}</label>
            <div class="col-sm-9">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="Groupe" placeholder="{{Adresse courte en hex sur 4 digits (ex: AE12)}}"/>
            </div>
        </div> -->

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Durée (s)}}</label>
            <div class="col-sm-9">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="onTime" placeholder="{{Durée en secondes}}"/>
            </div>
        </div>
    </div>

    <!-- telecommande7groups: visible / hidden
    <div class="form-group" id="telecommande7groups" style="display: none">
        <hr>

        <label>{{Télécommande 7 groupes}}</label></br>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Adresse groupe Tous}}</label>
            <div class="col-sm-9">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="GroupeEP1" placeholder="{{Adresse courte en hex sur 4 digits (ex: AE12)}}"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Adresse groupe 1}}</label>
            <div class="col-sm-9">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="GroupeEP3" placeholder="{{Adresse courte en hex sur 4 digits (ex: AE12)}}"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Adresse groupe 2}}</label>
            <div class="col-sm-9">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="GroupeEP4" placeholder="{{Adresse courte en hex sur 4 digits (ex: AE12)}}"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Adresse groupe 3}}</label>
            <div class="col-sm-9">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="GroupeEP5" placeholder="{{Adresse courte en hex sur 4 digits (ex: AE12)}}"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Adresse groupe 4}}</label>
            <div class="col-sm-9">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="GroupeEP6" placeholder="{{Adresse courte en hex sur 4 digits (ex: AE12)}}"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Adresse groupe 5}}</label>
            <div class="col-sm-9">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="GroupeEP7" placeholder="{{Adresse courte en hex sur 4 digits (ex: AE12)}}"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Adresse groupe 6}}</label>
            <div class="col-sm-9">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="GroupeEP8" placeholder="{{Adresse courte en hex sur 4 digits (ex: AE12)}}"/>
            </div>
        </div>
    </div> -->

    <!-- paramABC: visible / hidden -->
    <div class="form-group" id="paramABC" style="display: none">

        <label>{{Calibration (y=ax2+bx+c)}}</label></br>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Paramètre A}}</label>
            <div class="col-sm-9">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="paramA" placeholder="{{nombre}}"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Paramètre B}}</label>
            <div class="col-sm-9">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="paramB" placeholder="{{nombre}}"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Paramètre C}}</label>
            <div class="col-sm-9">
                <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="paramC" placeholder="{{nombre}}"/>
            </div>
        </div>
    </div>
