<?php

namespace LouvreBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use LouvreBundle\Entity\Commande;
use LouvreBundle\Form\CommandeType;
use LouvreBundle\Form\ConsultType;


class LouvreController extends Controller
{

    /**
     * @Route("/", name="home")
     */
    public function indexAction(Request $request)

    {
        $locale = $request->getLocale();
        return $this->render('LouvreBundle:home:index.html.twig', array('locale' => $locale));
    }


  /**
     * @Route("/droits", name="droits")
     */
    public function droitsAction(Request $request)
    {
        return $this->render('LouvreBundle:home:droits.html.twig');
    }


    /**
     * @Route("/commande", name="createCommande")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function commandeAction(Request $request)
    {
        $commande = new Commande();
        $form = $this->get('form.factory')->create(CommandeType::class,$commande);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $serial = $this->get('louvre.commandeserial')->createSerial();
            $commande->setName($serial);
            $tickets = $form->get('tickets')->getData();
            foreach ($tickets as $ticket) {
                $price = $this->get('louvre.ticketprice')->pricing($ticket->getBirth(), $ticket->getDiscount());
                $ticket->setCommande($commande);
                $ticket->setPrice($price);
            }
            $em->persist($commande);
            $em->flush();
            return $this->redirectToRoute('resumeCommande', array('name' => $commande->getName()));
        }
        $daysOff = $this->get('louvre.daysoff')->daysOff();
        return $this->render('LouvreBundle:commande:etape1.html.twig', array('form' => $form->createView(), 'daysoff' => $daysOff));
    }

    /**
     * @Route("/commande/edit/{name}", name="editCommande")
     * @param Request $request
     * @param Commande $commande
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function commandeEditAction(Request $request, Commande $commande)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(CommandeType::class,$commande);
        $originalTickets = new ArrayCollection();
        foreach ($commande->getTickets() as $ticket){
            $originalTickets->add($ticket);
        }
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            foreach ($originalTickets as $ticket) {
                if (false === $commande->getTickets()->contains($ticket)) {
                    $em->remove($ticket);
                }
            }
            $tickets = $form->get('tickets')->getData();
            foreach ($tickets as $ticket) {
                $price = $this->get('louvre.ticketprice')->pricing($ticket->getBirth(), $ticket->getDiscount());
                $ticket->setCommande($commande);
                $ticket->setPrice($price);
            }
            $commande->setStatus($commande::COMMANDE_MODIFIED);
            $em->persist($commande);
            $em->flush();

            return $this->redirectToRoute('resumeCommande', array('name' => $commande->getName()));
        }

        if (($commande->getStatus() != "pay_success")) {
        $daysOff = $this->get('louvre.daysoff')->daysOff();

        return $this->render('LouvreBundle:commande:etape1.html.twig', array('form' => $form->createView(), 'commande' => $commande, 'daysoff' => $daysOff));
    } else { return $this->redirectToRoute('home'); }
    }


    /**
     * @Route("/commande/resume/{name}", name="resumeCommande")
     * @param Request $request
     * @param Commande $commande
     * @return Response
     */
    public function commandeResumeAction(Request $request, Commande $commande)
    {
        $commandeAmount = $this->container->get('louvre.commandetotalprice')->calculatePrice($commande);
        if ($request->isMethod('POST')) {
            $token = $request->request->get('stripeToken');
            $paiementResult = $this->container->get('louvre.commandestripecharge')->commandeCharge($commande, $commandeAmount, $token);
            $em = $this->getDoctrine()->getManager();
            if ($paiementResult == "ok") {
                $commande->setStatus($commande::COMMANDE_PAYED);
                $this->get('louvre.commandemail')->sendMail($commande, $commandeAmount);
            }
            else {
                $commande->setStatus($commande::COMMANDE_PAY_PB);
            }
            $em->persist($commande);
            $em->flush();
        }
        return $this->render('LouvreBundle:commande:etape2.html.twig', array('commande' => $commande, 'commandeAmount'=> $commandeAmount));
    }

}
