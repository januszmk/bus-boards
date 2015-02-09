<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        $busStops = $this->getDoctrine()->getRepository("AppBundle:BusStop")->findAll();

        return array(
            "busStops"  =>  $busStops,
        );
    }

    /**
     * @Route("/getEntries", name="get_entries")
     */
    public function busStopEntriesAction(Request $request)
    {
        $busStop = $request->get("bus_stop");

        $busStop = $this->getDoctrine()->getRepository("AppBundle:BusStop")->findOneBy(array("name" => $busStop));

        $serializer = $this->get('serializer');

        if (!$busStop) {
            return new JsonResponse($serializer->serialize(array("entries" => array()), 'json'));
        }
        $busEntries = $this->getDoctrine()->getRepository("AppBundle:BusEntry")->findBusEntries($busStop);

        return new JsonResponse($serializer->serialize(array("entries" => $busEntries), "json"));



    }
}
