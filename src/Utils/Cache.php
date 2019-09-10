<?php

namespace App\Utils;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Cache {

    public function __construct(){

    }

    public function toCache($dataToCache, $target, $id){

        /*
        Création du cache.
        */
        $cache = new FilesystemAdapter(

            $namespace = '',
            $defaultLifetime = 20
        );
        
        /*
        Récupère si il existe l'item data.id et le créé si il n'existe pas.
        */
        $data = $cache->getItem('data-'.$target.$id);

        if (!$data->isHit()){
            
            $data->set(file_get_contents("$dataToCache"));
            $cache->save($data);
            $response = $data->get();

            return $response;

        } else {

            $response = $data->get();
            
            return $response;
        }
    }
}