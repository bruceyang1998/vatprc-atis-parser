<?php
require_once 'vendor/autoload.php';

use MetarDecoder\MetarDecoder;

$raw_metar = $_GET['metar'];
$decoder = new MetarDecoder();
$d = $decoder->parse($raw_metar);

/*//context information
$d->isValid(); //true
$d->getRawMetar(); //'METAR LFPO 231027Z AUTO 24004G09MPS 2500 1000NW R32/0400 R08C/0004D +FZRA VCSN //FEW015 17/10 Q1009 REFZRA WS R03'
$d->getType(); //'METAR'
$d->getIcao(); //'LFPO'
$d->getDay(); //23
$d->getTime(); //'10:27 UTC'
$d->getStatus(); //'AUTO'

//surface wind*/
$sw = $d->getSurfaceWind(); //SurfaceWind object
/*
$sw->getMeanSpeed()->getValue(); //4
$sw->getSpeedVariations()->getValue(); //9
$sw->getMeanSpeed()->getUnit(); //'m/s'

//visibility*/
$v = $d->getVisibility(); //Visibility object
/*$v->getVisibility()->getValue(); //2500
$v->getVisibility()->getUnit(); //'m'
$v->getMinimumVisibility()->getValue(); //1000
$v->getMinimumVisibilityDirection(); //'NW'
$v->hasNDV(); //false

//runway visual range*/
$rvr = $d->getRunwaysVisualRange(); //RunwayVisualRange array
/*$rvr[0]->getRunway(); //'32'
$rvr[0]->getVisualRange()->getValue(); //400
$rvr[0]->getPastTendency(); //''
$rvr[1]->getRunway(); //'08C'
$rvr[1]->getVisualRange()->getValue(); //4
$rvr[1]->getPastTendency(); //'D'

//present weather*/
$pw = $d->getPresentWeather(); //WeatherPhenomenon array
/*$pw[0]->getIntensityProximity(); //'+'
$pw[0]->getCharacteristics(); //'FZ'
$pw[0]->getTypes(); //array('RA')
$pw[1]->getIntensityProximity(); //'VC'
$pw[1]->getCharacteristics(); //null
$pw[1]->getTypes(); //array('SN')

// clouds*/
$cld = $d->getClouds(); //CloudLayer array
/*$cld[0]->getAmount(); //'FEW'
$cld[0]->getBaseHeight()->getValue(); //1500
$cld[0]->getBaseHeight()->getUnit(); //'ft'

// temperature
$d->getAirTemperature()->getValue(); //17
$d->getAirTemperature()->getUnit(); //'deg C'
$d->getDewPointTemperature()->getValue(); //10

// pressure
$d->getPressure()->getValue(); //1009
$d->getPressure()->getUnit(); //'hPa'

// recent weather
$rw = $d->getRecentWeather();
$rw->getCharacteristics(); //'FZ'
$rw->getTypes(); //array('RA')

// windshears
$d->getWindshearRunways(); //array('03')*/

if ($d->isValid() == false) {
    exit('Invalid METAR.');
}
print('[' . $d->getIcao() . '][info]' . $_GET['info'] . ' ' . substr($raw_metar, 7, 4) . '[UTC]');
if (strpos($d->getTime(), ':00') === false and strpos($d->getTime(), ':30') === false) {
    print('[special]');
}
if ($_GET['dep'] == $_GET['arr']) {
    print ('[runway]' . $_GET['dep']);
} else {
    print('[departure runway]' . $_GET['dep'] . '[landing runway]' . $_GET['arr']);
}
print('[expect][' . $_GET['apptype'] . '][approach][runway]' . $_GET['arr'] . '[wind]');

if ($sw->getMeanSpeed()->getValue() == 0) {
    print('[calm]');
} else {
    if ($sw->withVariableDirection() == true) {
        print('[variable]');
    } else {
        if ($sw->getMeanDirection()->getValue() < 100) {
            print('0');
        }
        print($sw->getMeanDirection()->getValue() . '[degrees]');
    }
    print('[at]' . $sw->getMeanSpeed()->getValue() . '[' . $sw->getMeanSpeed()->getUnit() . ']');
}
If ($sw->getSpeedVariations() != null) {
    print('[gusting to]' . $sw->getSpeedVariations()->getValue() . '[' . $sw->getMeanSpeed()->getUnit() . ']');
}
if ($sw->getDirectionVariations() != null) {
    print('[variable between]');
    if ($sw->getDirectionVariations()[0]->getValue() < 100) {
        print('0');
    }
    print($sw->getDirectionVariations()[0]->getValue() . '[and]');
    if ($sw->getDirectionVariations()[1]->getValue() < 100) {
        print('0');
    }
    print($sw->getDirectionVariations()[1]->getValue() . '[degrees]');
}
if (strpos($raw_metar, 'CAVOK') !== false) {
    print('[CAVOK]');
} else {
    print('[visibility]');
    if ($v->getVisibility()->getValue() == 9999) {
        print('10[kilometers]');
    } else {
        print('{' . $v->getVisibility()->getValue() . '}[');
        switch ($v->getVisibility()->getUnit()) {
            case 'm':
                print('meter');
                break;
            case 'SM':
                print('mile');
                break;
        }
        if ($v->getVisibility()->getValue() == 1) {
            print(']');
        } else {
            print('s]');
        }
    }
    if ($rvr != null) {
        foreach ($rvr as $rvrn) {
            print('[runway]' . $rvrn->getRunway() . '[RVR]');
            if ($rvrn->getVisualRange() == null) {
                print('[variable between]{' . $rvrn->getVisualRangeInterval()[0]->getValue() . '}[and]{' . $rvrn->getVisualRangeInterval()[1]->getValue() . '}[');
                if ($rvrn->getVisualRangeInterval()[0]->getUnit() == 'ft') {
                    print('feet]');
                } elseif ($rvrn->getVisualRangeInterval()[0]->getUnit() == 'm') {
                    print('meter');
                    if ($rvrn->getVisualRangeInterval()[0]->getValue() == 1) {
                        print(']');
                    } else {
                        print('s]');
                    }
                }
            } else {
                print('{' . $rvrn->getVisualRange()->getValue() . '}[');
                if ($rvrn->getVisualRange()->getUnit() == 'ft') {
                    print('feet]');
                } elseif ($rvrn->getVisualRange()->getUnit() == 'm') {
                    print('meter');
                    if ($rvrn->getVisualRange()->getValue() == 1) {
                        print(']');
                    } else {
                        print('s]');
                    }
                }
            }
            switch ($rvrn->getPastTendency()) {
                case 'D':
                    print('[downward]');
                    break;
                case 'N':
                    print('[no change]');
                    break;
                case 'U':
                    print('[upward]');
                    break;
            }
        }
    }
    if (strpos($raw_metar, 'NSC') === true) {
        print('[no significant clouds]');
    }
    foreach ($pw as $pwn) {
        if ((string)$pwn->getIntensityProximity() !== '') {
            switch ((string)$pwn->getIntensityProximity()) {
                case '+':
                    print('[heavy]');
                    break;
                case '-':
                    print('[light]');
                    break;
            }
        }
        if ($pwn->getCharacteristics() !== '') {
            switch ($pwn->getCharacteristics()) {
                case 'MI':
                    print('[shallow]');
                    break;
                case 'BC':
                    print('[patches]');
                    break;
                case 'PR':
                    print('[partial]');
                    break;
                case 'DR':
                    print('[drifting]');
                    break;
                case 'BL':
                    print('[blowing]');
                    break;
                case 'SH':
                    print('[showers]');
                    break;
                case 'TS':
                    print('[thunderstorm]');
                    break;
                case 'FZ':
                    print('[freezing]');
                    break;
            }
        }
        foreach ($pwn->getTypes() as $pwntype) {
            switch ($pwntype) {
                case 'DZ':
                    print('[drizzle]');
                    break;
                case 'RA':
                    print('[rain]');
                    break;
                case 'SN':
                    print('[snow]');
                    break;
                case 'SG':
                    print('[snow grains]');
                    break;
                case 'IC':
                    print('[ice crystals]');
                    break;
                case 'PL':
                    print('[ice pellets]');
                    break;
                case 'GR':
                    print('[hail]');
                    break;
                case 'GS':
                    print('[snow pellets]');
                    break;
                case 'UP':
                    print('[unknown precipitation]');
                    break;
                case 'BR':
                    print('[mist]');
                    break;
                case 'FG':
                    print('[fog]');
                    break;
                case 'FU':
                    print('[smoke]');
                    break;
                case 'VA':
                    print('[volcanic ash]');
                    break;
                case 'DU':
                    print('[dust]');
                    break;
                case 'SA':
                    print('[sand]');
                    break;
                case 'HZ':
                    print('[haze]');
                    break;
                case 'PO':
                    print('[dust whirls]');
                    break;
                case 'SQ':
                    print('[squalls]');
                    break;
                case 'FC':
                    print('[funnel cloud]');
                    break;
                case 'SS':
                    print('[sandstorm]');
                    break;
                case 'DS':
                    print('[duststorm]');
                    break;
            }
        }
        if ((string)$pwn->getIntensityProximity() == 'VC') {
            print('[in the vicinity]');
        }
    }
}

if (strpos($raw_metar, 'CLR') === true or strpos($raw_metar, 'SKC') === true) {
    print('[sky clear]');
}
foreach ($cld as $cldn) {
    switch ($cldn->getAmount()) {
        case 'FEW':
            print('[few]');
            break;
        case 'SCT':
            print('[scattered]');
            break;
        case 'BKN';
            print('[broken]');
            break;
        case 'OVC';
            print('[overcast]');
            break;
    }
    switch ($cldn->getBaseHeight()->getUnit()) {
        case 'ft';
            print('{' . $cldn->getBaseHeight()->getValue() * 0.3 . '}' . '[meters]');
    }
    switch ($cldn->getType()) {
        case 'CB':
            print('[cumulonimbus]');
            break;
        case 'TCU':
            print('[towering cumulus]');
            break;
    }
}
print('[temperature]' . $d->getAirTemperature()->getValue() .
    '[' . $d->getAirTemperature()->getUnit() . '][dewpoint]' . $d->getDewPointTemperature()->getValue() .
    '[' . $d->getDewPointTemperature()->getUnit() . '][QNH]' .
    $d->getPressure()->getValue() . '[' . $d->getPressure()->getUnit() . ']');
print('[advise on initial contact you have info]' . $_GET['info'] . '[and confirm you will implement RNAV procedures]');
/*print('[ATIS.gq Alpha Version, NOT FOR OPERATIONAL USE]');*/