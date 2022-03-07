<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationService
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * permet de récupérer le language dans la request en|fr
     *
     * @param Request $request
     * @return string
     */
    public function getLanguage(Request $request, $user)
    {
        if($request->headers->has('language')) {
            if ($request->headers->get('language') == "fr") {
                return "fr_FR";
            }

            if ($request->headers->get('language') == "en") {
                return "en_GB";
            }
        }

        // if ($user && $user->getLanguage() != null) {
        //     return $user->getLanguage();
        // }

        $datas = json_decode($request->getContent(), true);

        if (isset($datas["language"]) && $datas["language"] == "en") {
            return "en_GB";
        } elseif (isset($datas["language"]) && $datas["language"] == "fr") {
            return "fr_FR";
        } elseif ($request->get("language") == "en") {
            return "en_GB";
        } elseif ($request->get("language") == "fr") {
            return "fr_FR";
        } elseif ($request->getLocale() == "en") {
            return "en_GB";
        }

        return "fr_FR";
    }

    /**
     * permet de traduire un message
     *
     * @param string $message
     * @return string
     */
    public function trans($message, $args = [], $language = "fr_FR")
    {
        return $this->translator->trans($message, $args, 'messages', $language);
    }

}