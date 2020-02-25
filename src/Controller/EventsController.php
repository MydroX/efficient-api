<?php

namespace App\Controller;

use App\Entity\Defect;
use App\Entity\Event;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class EventsController extends AbstractController
{
    /**
     * @Route(name="events", path="api/events", methods={"GET"})
     * @SWG\Get(
     *     path="/api/events",
     *     summary="Get all events",
     *     operationId="getEvents",
     *     produces={"application/json"},
     *     description="Returns all events",
     *     @SWG\Response(
     *          response="200",
     *          description="Success",
     *          @Model(type=Event::class)
     *     )
     * )
     */
    public function getEvents()
    {
        $eventRepository = $this->getDoctrine()->getRepository(Event::class);
        $events = $eventRepository->findAll();

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($events, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonContent, 200, ['Content-Type' => 'application/json', 'Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route(name="eventsByDateId", path="api/events/{date_id}", methods={"GET"})
     * @SWG\Get(
     *     path="/api/events/{date_id}",
     *     summary="Get events by date id",
     *     operationId="getEventsByDateId",
     *     produces={"application/json"},
     *     description="Returns every events for the given date id",
     *     @SWG\Response(
     *          response="200",
     *          description="Success",
     *          @Model(type=Event::class)
     *     )
     * )
     */
    public function getEventsByDateId(Request $request) {
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $events = $eventRepo->findBy(["dateId" => $request->get("date_id")]);

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $jsonObject = $serializer->serialize($events, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        return new Response($jsonObject, 200, ['Content-Type' => 'application/json', 'Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @param Request $request
     *
     * @Route(name="CountEventsByDistrict", path="api/events/count/district/{district_id}", methods={"GET"})
     * @todo doc swagger
     *
     * @return Response
     */
    public function getCountEventsByDistrict(Request $request) {
        $district = $request->get("district_id");
        $districtFrontId = "d".$district;

        $eventPlaceRepository = $this->getDoctrine()->getRepository(Event::class);
        $events = $eventPlaceRepository->findCountForEveryDate($district);

        $data = ["district" => $districtFrontId, "days" => $events];

        $encoders = [new JsonEncoder()];
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $jsonContent = $serializer->serialize($data, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($jsonContent, 200, ['Content-Type' => 'application/json', 'Access-Control-Allow-Origin' => '*']);
    }
}
