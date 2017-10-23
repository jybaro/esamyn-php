<?php

function array_to_xml( $data, &$xml_data = null) {
    $primero = false;
    if (empty($xml_data)) {
        $primero = true;
        $xml_data = new SimpleXMLElement('<p></p>');
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }
    foreach( $data as $key => $value ) {
        if( is_numeric($key) ){
            $key = 'item'.$key; //dealing with <0/>..<n/> issues
        }
        if( is_array($value) ) {
            $subnode = $xml_data->addChild($key);
            array_to_xml($value, $subnode);
        } else {
            $xml_data->addChild("$key",htmlspecialchars("$value"));
        }
    }
    if ($primero) {
        return $xml_data->asXML();
    }
}

function p_formatear_fecha($timestamp){
    date_default_timezone_set('America/Guayaquil');
    setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
    $fecha = strftime("%A %d de %B de %Y a las %Hh%S", strtotime($timestamp));
    //$fecha = htmlspecialchars($fecha);
    $fecha = utf8_encode($fecha);
    return $fecha;
}
