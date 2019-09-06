<?php

namespace App\Utils;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Cache {

    public function __construct(){

    }

    public function toCache($dataToCache, $id){

        dump($dataToCache);
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
        $data = $cache->getItem('data'.$id);
        dump($data);
        /*

        */
        if (!$data->isHit()){
            
            $data->set(file_get_contents("$dataToCache"));
            $cache->save($data);
            $response = $data->get();

            return $response;

        } else {

            // $json = file_get_contents("http://api.tvmaze.com/shows/".$showId);
            $response = $data->get();

            
            return $response;
        }
    }
}