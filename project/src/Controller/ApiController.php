<?php

namespace App\Controller;

use App\Http\ApiResponse;
use App\Service\TranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenFoodFacts\Api;

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
     * Enregistrement
     * 
     * @Route("/api/search/name", name="search", methods={"GET"})
     * 
     * @param Request $request
     * @return ApiResponse
     */
    public function search(Request $request): ApiResponse
    {


//         - /search/name?name=nom_du_produit&ean=...&marque=...
// Au moins un critère de recherche est obligatoire.
// Lors de la requête /search, l’API doit retourner les produits correspondants aux critères en excluant la
// liste d’exclusion de l’utilisateur s’il y en a une.

        $api = new Api('food','fr');

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

        return new ApiResponse("", $datas); 
    }
}