<?php
require_once 'vendor/autoload.php';

use MetarDecoder\MetarDecoder;

$raw_metar = $_GET['metar'];
$decoder = new MetarDecoder();
$d = $decoder->parse($raw_metar);
//print($d->getCavok().PHP_EOL);
//print($d->getAirTemperature().PHP_EOL);

/*$raw_dump = util::var_dump($d,true,2);
$to_delete=array(
    'private:MetarDecoder\\Entity\\DecodedMetar:',
    'private:MetarDecoder\\Entity\\',
    'MetarDecoder\\Entity\\',
    'Value:'
);
$clean_dump = str_replace($to_delete,'',$raw_dump);
echo $clean_dump;*/

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

//runway visual range
$rvr = $d->getRunwaysVisualRange(); //RunwayVisualRange array
$rvr[0]->getRunway(); //'32'
$rvr[0]->getVisualRange()->getValue(); //400
$rvr[0]->getPastTendency(); //''
$rvr[1]->getRunway(); //'08C'
$rvr[1]->getVisualRange()->getValue(); //4
$rvr[1]->getPastTendency(); //'D'

//present weather
$pw = $d->getPresentWeather(); //WeatherPhenomenon array
$pw[0]->getIntensityProximity(); //'+'
$pw[0]->getCharacteristics(); //'FZ'
$pw[0]->getTypes(); //array('RA')
$pw[1]->getIntensityProximity(); //'VC'
$pw[1]->getCharacteristics(); //null
$pw[1]->getTypes(); //array('SN')

// clouds
$cld = $d->getClouds(); //CloudLayer array
$cld[0]->getAmount(); //'FEW'
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

if ($d->isValid()==false){
    exit('Invalid METAR.');
}
print( '['.$d->getIcao().'][info]'.$_GET['info'].' '.substr($raw_metar, 7, 4). '[UTC]');
if(strpos($d->getTime(),':00')===false and strpos($d->getTime(),':30')===false){
    print('[special]');
}
if($_GET['dep']==$_GET['arr']){
    print ('[runway]'.$_GET['dep']);
}else{
    print('[departure runway]' . $_GET['dep'] . '[landing runway]' . $_GET['arr']);
}
print('[expect][' . $_GET['apptype'] . '][approach][runway]' . $_GET['arr'].'[wind]');

switch (true) {
    case $sw->getMeanSpeed()->getConvertedValue(\MetarDecoder\Entity\Value::METER_PER_SECOND)<=2:
        print('[calm]');
        break;
    case $sw->getMeanSpeed()->getConvertedValue(\MetarDecoder\Entity\Value::METER_PER_SECOND)>2:
        if($sw->withVariableDirection()==true){
            print('[variable]');
        }else{
            if($sw->getMeanDirection()->getValue()<100){
                print('0');}
            print($sw->getMeanDirection()->getValue(). '[degrees]');
        }
        print('[at]' .$sw->getMeanSpeed()->getValue().'['.$sw->getMeanSpeed()->getUnit().']');
        break;}
If($sw->getSpeedVariations()!==null){
        print('[gusting to]'.$sw->getSpeedVariations()->getValue().'['.$sw->getMeanSpeed()->getUnit().']');
}
switch (true) {
    case strpos($raw_metar, 'CAVOK')!==false:
        print('[CAVOK]');
        break;
    case (strpos($raw_metar, 'CLR')!==false or strpos($raw_metar, 'SKC')!==false):
        print('[sky clear]');
        break;
    default:
        print('[visibility]');
        if ($v->getVisibility()->getValue()==9999) {
            print('10[kilometers]');
        }
        else{
            print('{'.$v->getVisibility()->getValue().'}[');
            switch ($v->getVisibility()->getUnit()){
                case 'm':
                    print('meter');
                    break;
                case 'sm':
                    print('mile');
                    break;
            }
            if ($v->getVisibility()->getValue() == 1) {
                print(']');
            }else{
                print('s]');
            }
        }
        if (strpos($raw_metar, 'NSC')!==false){
            print('[no significant clouds]');
        }
        $i=0;
        $pw=$d->getPresentWeather();
        foreach($pw as $pwn){
            if ((string)$pwn->getIntensityProximity() !== ''){
                switch ((string)$pwn->getIntensityProximity()){
                    case '+':
                        print('[heavy]');
                        break;
                    case '-':
                        print('[light]');
                        break;
                    case 'VC':
                        print('[in the vicinity]');

                }
            }
            if ($pwn->getCharacteristics() !== ''){
                switch($pwn->getCharacteristics()){
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
                print('['.$pwn->getCharacteristics().']');
            }
            foreach($pwn->getTypes() as $pwntype){
                switch ($pwntype){
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
        }
}
print('ATIS.gq Alpha Version, NOT FOR OPERATIONAL USE');
/*while($i!=count($d->getPresentWeather())){
    print ($pw[$i]->getTypes());
    $i++;}
}*/
/*print('[TEMPERATURE]'. $d->getAirTemperature()->getValue().
    '['.$d->getAirTemperature()->getUnit().']
    [DEWPOINT]'.$d->getDewPointTemperature()->getValue().
    '['.$d->getDewPointTemperature()->getUnit().']');*/