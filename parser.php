<?php
require_once 'vendor/autoload.php';
use MetarDecoder\MetarDecoder;

$rawMetar = $_GET['metar'];
$decoder = new MetarDecoder();
$decoded = $decoder->parse($rawMetar);
$surfaceWindObj = $decoded->getSurfaceWind(); //SurfaceWind object
$visObj = $decoded->getVisibility(); //Visibility object
$rvr = $decoded->getRunwaysVisualRange(); //RunwayVisualRange array
$phenomenon = $decoded->getPresentWeather(); //WeatherPhenomenon array
$clouds = $decoded->getClouds(); //CloudLayer array

if ($decoded->isValid() == false) {
    exit('Invalid METAR.');
}
print('[' . $decoded->getIcao() . '][info]' . $_GET['info'] . ' ' . substr($rawMetar, 7, 4) . '[UTC]');
if (strpos($decoded->getTime(), ':00') === false and strpos($decoded->getTime(), ':30') === false) {
    print('[special]');
}
if ($_GET['dep'] == $_GET['arr']) {
    print ('[runway]' . $_GET['dep']);
} else {
    print('[departure runway]' . $_GET['dep'] . '[landing runway]' . $_GET['arr']);
}
print('[expect][' . $_GET['apptype'] . '][approach][runway]' . $_GET['arr'] . '[wind]');

if ($surfaceWindObj->getMeanSpeed()->getValue() == 0) {
    print('[calm]');
} else {
    if ($surfaceWindObj->withVariableDirection() == true) {
        print('[variable]');
    } else {
        if ($surfaceWindObj->getMeanDirection()->getValue() < 100) {
            print('0');
        }
        print($surfaceWindObj->getMeanDirection()->getValue() . '[degrees]');
    }
    print('[at]' . $surfaceWindObj->getMeanSpeed()->getValue() . '[' . $surfaceWindObj->getMeanSpeed()->getUnit() . ']');
}
If ($surfaceWindObj->getSpeedVariations() != null) {
    print('[gusting to]' . $surfaceWindObj->getSpeedVariations()->getValue() . '[' . $surfaceWindObj->getMeanSpeed()->getUnit() . ']');
}
if ($surfaceWindObj->getDirectionVariations() != null) {
    print('[variable between]');
    if ($surfaceWindObj->getDirectionVariations()[0]->getValue() < 100) {
        print('0');
    }
    print($surfaceWindObj->getDirectionVariations()[0]->getValue() . '[and]');
    if ($surfaceWindObj->getDirectionVariations()[1]->getValue() < 100) {
        print('0');
    }
    print($surfaceWindObj->getDirectionVariations()[1]->getValue() . '[degrees]');
}
if (strpos($rawMetar, 'CAVOK') !== false) {
    print('[CAVOK]');
} else {
    print('[visibility]');
    if ($visObj->getVisibility()->getValue() == 9999) {
        print('10[kilometers]');
    } else {
        print('{' . $visObj->getVisibility()->getValue() . '}[');
        switch ($visObj->getVisibility()->getUnit()) {
            case 'm':
                print('meter');
                break;
            case 'SM':
                print('mile');
                break;
        }
        if ($visObj->getVisibility()->getValue() == 1) {
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
                    break;
                case 'U':
                    print('[upward]');
                    break;
            }
        }
    }
    if (strpos($rawMetar, 'NSC') === true) {
        print('[no significant clouds]');
    }
    foreach ($phenomenon as $pwn) {
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
if (strpos($rawMetar, 'CLR') === true or strpos($rawMetar, 'SKC') === true) {
    print('[sky clear]');
}
foreach ($clouds as $cldn) {
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
print('[temperature]' . $decoded->getAirTemperature()->getValue() .
    '[' . $decoded->getAirTemperature()->getUnit() . '][dewpoint]' . $decoded->getDewPointTemperature()->getValue() .
    '[' . $decoded->getDewPointTemperature()->getUnit() . '][QNH]' .
    $decoded->getPressure()->getValue() . '[' . $decoded->getPressure()->getUnit() . ']');
print('[advise on initial contact you have info]' . $_GET['info'] . '[and confirm you will implement RNAV procedures]');