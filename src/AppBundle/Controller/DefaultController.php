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
        return array(
        );
    }

    /**
     * @Route("/getBusStops", name="get_bus_stops")
     */
    public function busStopsAction()
    {
        $serializer = $this->get('serializer');

        return new JsonResponse(
            $serializer->serialize($this->get('bus_service')->getBusStops(), "json")
        );
    }

    /**
     * @Route("/getEntries", name="get_entries")
     */
    public function busStopEntriesAction(Request $request)
    {
        $busStop = $request->get("bus_stop");

        if (!$busStop) {
            throw $this->createNotFoundException();
        }
        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->serialize(array("entries" => $this->get('bus_service')->getEntries($busStop)), "json"));

    }
}
