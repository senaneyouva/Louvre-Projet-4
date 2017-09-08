<?php
namespace Tests\LouvreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LouvreControllerTest extends WebTestCase
{

    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Louvre")')->count()
        );
    }


    public function testErrorResumeOrder()
    {
        $client = self::createClient();
        $client->request('GET', '/commande/resume/RS06');

        $this->assertTrue($client->getResponse()->isNotFound());
    }




    public function urlProvider()
    {
        return array(
            array('/'),
            array('/commande'),
          //  array('commande/resume/RS031563'),
      
        );
    }


    public function testOrderSearch()
    {
        $client = static::createClient();
        $crawler = $client->request('GET');


        $link = $crawler
            ->filter('a:contains("billets")')
            ->eq(0)
            ->link()
        ;
        $crawler = $client->click($link);

        $buttonCrawlerNode = $crawler->selectButton('formSend');
        $form = $buttonCrawlerNode->form();


        $form['commande[date]'] = '31/08/2017';
        $form['commande[email]'] = 'senaneyouva@outlook.fr';

        $values = $form->getPhpValues();

        $values['commande']['tickets'][0]['name'] = 'Youva';
        $values['commande']['tickets'][0]['surname'] = 'Senane';
        $values['commande']['tickets'][0]['birth'] = '20/20/1993';
        $values['commande']['tickets'][0]['country'] = 'FR';

        $crawler = $client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());
   // dump($crawler);
       $crawler = $client->followRedirect();

        if ($this->assertEquals(1, $crawler->filter('html:contains("Récapitulatif")')->count())) {
            echo 'Formulaire validé - >page recapitulatif';
        }



        $link = $crawler
            ->filter('a:contains("commande")')
            ->eq(0)
            ->link()
        ;
        $crawler = $client->click($link);
        if ($this->assertEquals(1, $crawler->filter('html:contains("naissance")')->count())) {
            echo 'Retour -> formulaire via bouton';
        }
        // dump($crawler);
        // $this->assertEquals(1, $crawler->filter('html:contains("naissance")')->count());
        $buttonCrawlerNode = $crawler->selectButton('formSend');
        $form = $buttonCrawlerNode->form();
        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        // $this->assertEquals(1, $crawler->filter('html:contains("Récapitulatif")')->count());
        if ($this->assertEquals(1, $crawler->filter('html:contains("Récapitulatif")')->count())) {
            echo 'Formulaire validé - >page recapitulatif';
        }

    }


    public function testOrderFailEmail()
    {
        $client = static::createClient();
        $crawler = $client->request('GET');


        $link = $crawler
            ->filter('a:contains("billets")')
            ->eq(0)
            ->link()
        ;
        $crawler = $client->click($link);

        $buttonCrawlerNode = $crawler->selectButton('formSend');
        $form = $buttonCrawlerNode->form();


        $form['commande[date]'] = '31/08/2017';
         $form['commande[email]'] = 'senaneyouva@outlook.fr';

        $values = $form->getPhpValues();

        $values['commande']['tickets'][0]['name'] = 'Senane';
        $values['commande']['tickets'][0]['surname'] = 'Youva';
        $values['commande']['tickets'][0]['birth'] = '20/08/1993';
        $values['commande']['tickets'][0]['country'] = 'FR';

        $crawler = $client->request($form->getMethod(), $form->getUri(), $values,
            $form->getPhpFiles());
      //  $crawler = $client->followRedirect();
        // dump($crawler);
      $this->assertEquals(1, $crawler->filter('html:contains("est pas valide...")')->count());

    }


    public function testOrderFailTicketName()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/fr/');


        $link = $crawler
            ->filter('a:contains("billets")')
            ->eq(0)
            ->link()
        ;
        $crawler = $client->click($link);

        $buttonCrawlerNode = $crawler->selectButton('formSend');
        $form = $buttonCrawlerNode->form();


        $form['commande[date]'] = '28/10/2016';
        $form['commande[email]'] = 'contact@blagache.com';

        $values = $form->getPhpValues();

        $values['commande']['tickets'][0]['name'] = 'S'; // l'Erreur est ici !
        $values['commande']['tickets'][0]['surname'] = 'Youva';
        $values['commande']['tickets'][0]['birth'] = '30/08/1993';
        $values['commande']['tickets'][0]['country'] = 'FR';

        $crawler = $client->request($form->getMethod(), $form->getUri(), $values,
            $form->getPhpFiles());
        //  $crawler = $client->followRedirect();
        // dump($crawler);
        $this->assertEquals(1, $crawler->filter('html:contains("caractères")')->count());

    }

}