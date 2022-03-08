<?php

namespace App\Controller;

use App\Entity\Exclusion;
use App\Entity\Favori;
use App\Entity\User;
use App\Http\ApiResponse;
use App\Service\TranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenFoodFacts\Api;

/**
* 
* @Route("/api") 
*/
class ApiController extends AbstractController
{
    private $em;
    private $translatorService;

    public function __construct(
        EntityManagerInterface $em,
        TranslationService $translatorService
    ) {
        $this->em = $em;
        $this->translatorService = $translatorService;
    }


    /**
     * l’API doit sauvegarder en base de données le favori pour l’utilisateur qui a requete
     * 
     * @Route("/save", name="save", methods={"POST"})
     * 
     * @param Request $request
     * @return ApiResponse
     */
    public function save(Request $request): ApiResponse
    {
        $attr = json_decode($request->getContent(), true);
        $ean = $attr["ean"];

        $api = new Api('food','fr');
        $product = $api->getProduct($ean);

        if($product instanceof \OpenFoodFacts\Document) {

            if(!$favori = $this->em->getRepository(Favori::class)->findOneBy(["ean" => $ean])) {
                $favori = new Favori();
                $favori->setEan($ean);
                $this->em->persist($favori);
            }
    
            /**@var User $user */
            $user = $this->getUser();
            $user->addFavori($favori);
            $this->em->persist($user);
            
            $this->em->flush();
    
            return new ApiResponse("", ["code" => 200]);

        } 

        return $product;
    }


    /**
     * l’API doit sauvegarder le produit en base de données en tant que liste d’exclusion pour l’utilisateur qui a requêté
     * 
     * @Route("/exclude", name="exclude", methods={"POST"})
     * 
     * @param Request $request
     * @return ApiResponse
     */
    public function exclude(Request $request): ApiResponse
    {
        $attr = json_decode($request->getContent(), true);
        $ean = $attr["ean"];

        $api = new Api('food','fr');
        $product = $api->getProduct($ean);

        if ($product instanceof \OpenFoodFacts\Document) {

            if (!$exclusion = $this->em->getRepository(Exclusion::class)->findOneBy(["ean" => $ean])) {
                $exclusion = new Exclusion();
                $exclusion->setEan($ean);
                $this->em->persist($exclusion);
            }
      
            /**@var User $user */
            $user = $this->getUser();
            $user->addExclusion($exclusion);
            $this->em->persist($user);
        
            $this->em->flush();

            return new ApiResponse("", ["code" => 200]);
        }

        return $product;
    }

    /**
     * API doit supprimer en base de données le favori pour l’utilisateur qui a requêté.
     * 
     * @Route("/delete", name="delete", methods={"POST"})
     * 
     * @param Request $request
     * @return ApiResponse
     */
    public function delete(Request $request): ApiResponse
    {
        $attr = json_decode($request->getContent(), true);
        $ean = $attr["ean"];

        $api = new Api('food','fr');
        $product = $api->getProduct($ean);

        if ($product instanceof \OpenFoodFacts\Document) {

            /**@var User $user */
            $user = $this->getUser();
            $favori = $this->em->getRepository(Favori::class)->findOneBy(["ean" => $ean]);
            $user->removeFavori($favori);

            $this->em->flush();

            return new ApiResponse("", ["code" => 200]);
        }

        return $product;
    }

    /**
     * l’API doit vider en base de données les favoris pour l’utilisateur qui a requêté.
     * 
     * @Route("/clear", name="clear", methods={"POST"})
     * 
     * @param Request $request
     * @return ApiResponse
     */
    public function clear(Request $request): ApiResponse
    {
        $attr = json_decode($request->getContent(), true);
        $ean = $attr["ean"];

        /**@var User $user */
        $user = $this->getUser();
        $favoris = $user->getFavoris();

        if(count($favoris) > 0) {
            foreach($favoris as $favori) {
                $user->removeFavori($favori);
            }

            $this->em->flush();
        }

        return new ApiResponse("", ["code" => 200]);
    }

    /**
     * l’API doit retourner les produits correspondants aux critères en excluant la liste d’exclusion de l’utilisateur s’il y en a une.
     * 
     * @Route("/search/name", name="search", methods={"GET"})
     * 
     * @param Request $request
     * @return ApiResponse
     */
    public function search(Request $request): ApiResponse
    {
        $api = new Api('food','fr');

        // TODO: Au moins un critère de recherche est obligatoire.

        // critères
        // $ingredients = $request->get("ingredients");
        // $allergenes = $request->get("allergenes");
        // $nutriscore = $request->get("nutriscore");
        // $valeurs_nutri = $request->get("valeurs_nutri");

        // $nom = $request->get("nom");
        // $ean = $request->get('ean');
        // $marque = $request->get('marque');
        // $categorie = $request->get('categorie');

        $search = $request->get('search');


        $products = $api->search($search);

        $i = 0;
        foreach($products as $product){

            $data = $product->getData();
            
            dump("nom : " . $data["product_name"]);

           dump("EAN : " . $data["code"]);

           dump("ingredients : " . $data["ingredients_text_fr"]);

           dump('nutriscore : ' . $data["nutrition_grades"]);


           // TODO
            // "marque : ",
            // "allergenes : ",
            // "valeurs_nutri : ",
            // "meilleur_substitut : ",
            
            $i++;

        }

        dump($i);

        $datas = [
            "nom" => "",
            "EAN" => "",
            "marque" => "",
            "ingredients" => "",
            "allergenes" => "",
            "nutriscore" => "",
            "valeurs_nutri" => "",
            "meilleur_substitut" => ""
        ];

        // TODO: retourne la liste des produits en excluant la liste d'exclusion de l'user s'il en a une

        return new ApiResponse("", $datas); 
    }
}