<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
  public function show(FlattenException $exception, Request $request): Response
  {
    $statusCode = $exception->getStatusCode();
    if ($statusCode === 404) {
      return $this->render('bundles/TwigBundle/Exception/error404.html.twig', [
        'status_code' => $statusCode
      ]);
    }

    if ($statusCode === 403) {
      return $this->render('bundles/TwigBundle/Exception/error403.html.twig', [
        'status_code' => $statusCode
      ]);
    }

    return $this->render('bundles/TwigBundle/Exception/error.html.twig', [
      'status_code' => $statusCode
    ]);
  }
}
