<?php

namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class BusService
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return array of bus stops with every line and time
     */
//    public function getEntries()
//    {
//        $client = new Client();
//        $crawler = $client->request("GET", "http://rozklady.mpk.krakow.pl/aktualne/przystan.htm");
//        $entries = array();
////        foreach($crawler->filter("table a") as $linkNode) {
////            /** @var Crawler $linkNode */
////
////            $client->click($crawler->link($linkNode->textContent)->link());
////            foreach ($crawler->filter("a") as $lineNode) {
////                $crawler = $client->click($lineNode->link());
////                var_dump($client->getResponse());
////            }
////        }
////        die;
//        $crawler->filter("table a")->each(function ($node) use(&$entries, $client) {
//            if ($node->text()) {
//                $busStopName = $node->text();
//                $crawler = $client->click($node->link());
//                $crawler->filter("a")->each(function ($node) use(&$entries, $client, $busStopName) {
//                    $uri = $node->link()->getUri();
//                    $uriParts = explode("/", $uri);
//                    $uriParts[count($uriParts) - 1] = str_replace("r","t", $uriParts[count($uriParts) - 1]);
//                    $crawler = $client->request("GET", implode("/", $uriParts));
//                    $crawler = $crawler->filter('.celldepart tr');
////                    var_dump($crawler->filter(".cellhour")->html());
////                    if ($crawler->filter(".cellhour")->count()) {
////                        echo $crawler->filter(".cellhour")->count() . "\n";
////                    }
//                });
//
//            }
//        });
//        var_dump($entries);
//    }
}