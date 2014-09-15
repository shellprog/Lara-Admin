<?php

class Utils {

    public static function buildMessages($messages){

        $response="";

        foreach($messages as $message){
            $response.="<li>{$message}</li>";
        }

        return $response;
    }

    public static function timestamp(){
        $dt = new DateTime;
        return $dt->format('Y-m-d H:i:s');
    }

    static function object_to_array($obj) {
        if(is_object($obj)) $obj = (array) $obj;
        if(is_array($obj)) {
            $new = array();
            foreach($obj as $key => $val) {
                $new[$key] = Utils::object_to_array($val);
            }
        }
        else $new = $obj;
        return $new;
    }

} 