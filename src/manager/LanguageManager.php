<?php


namespace Terminal\Manager;

/**
 * Manager se chargeant de déterminer dans quelle langue doivent être les données
 * Class LanguageManager
 * @package Terminal\Manager
 */
class LanguageManager
{
    private static $_instance;
    private $lang;

    public static function getInstance(): LanguageManager
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new LanguageManager();
        }
        return self::$_instance;
    }


    /**
     * parse list of comma separated language tags and sort it by the quality value
     * @param $languageList
     * @return array
     */
    public function parseLanguageList($languageList = null)
    {
        if (is_null($languageList)) {
            if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                return array();
            }
            $languageList = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }
        $languages = array();
        $languageRanges = explode(',', trim($languageList));
        foreach ($languageRanges as $languageRange) {
            if (preg_match('/(\*|[a-zA-Z0-9]{1,8}(?:-[a-zA-Z0-9]{1,8})*)(?:\s*;\s*q\s*=\s*(0(?:\.\d{0,3})|1(?:\.0{0,3})))?/', trim($languageRange), $match)) {
                if (!isset($match[2])) {
                    $match[2] = '1.0';
                } else {
                    $match[2] = (string)floatval($match[2]);
                }
                if (!isset($languages[$match[2]])) {
                    $languages[$match[2]] = array();
                }
                $languages[$match[2]][] = strtolower($match[1]);
            }
        }
        krsort($languages);
        return $languages;
    }

    /**
     * compare two parsed arrays of language tags and find the matches
     * @param $accepted
     * @param $available
     * @return array
     */
    public function findMatches($accepted, $available)
    {
        $matches = array();
        $any = false;
        foreach ($accepted as $acceptedQuality => $acceptedValues) {
            $acceptedQuality = floatval($acceptedQuality);
            if ($acceptedQuality === 0.0) continue;
            foreach ($available as $availableQuality => $availableValues) {
                $availableQuality = floatval($availableQuality);
                if ($availableQuality === 0.0) continue;
                foreach ($acceptedValues as $acceptedValue) {
                    if ($acceptedValue === '*') {
                        $any = true;
                    }
                    foreach ($availableValues as $availableValue) {
                        $matchingGrade = $this->matchLanguage($acceptedValue, $availableValue);
                        if ($matchingGrade > 0) {
                            $q = (string)($acceptedQuality * $availableQuality * $matchingGrade);
                            if (!isset($matches[$q])) {
                                $matches[$q] = array();
                            }
                            if (!in_array($availableValue, $matches[$q])) {
                                $matches[$q][] = $availableValue;
                            }
                        }
                    }
                }
            }
        }
        if (count($matches) === 0 && $any) {
            $matches = $available;
        }
        krsort($matches);
        return $matches;
    }

    /**
     * compare two language tags and distinguish the degree of matching
     * @param $a
     * @param $b
     * @return float|int
     */
    public function matchLanguage($a, $b)
    {
        $a = explode('-', $a);
        $b = explode('-', $b);
        for ($i = 0, $n = min(count($a), count($b)); $i < $n; $i++) {
            if ($a[$i] !== $b[$i]) break;
        }
        return $i === 0 ? 0 : (float)$i / count($a);
    }

    public function include_LANG()
    {
        $accepted = $this->parseLanguageList();
        $available = $this->parseLanguageList('en, fr, de');
        $matches = $this->findMatches($accepted, $available);
        if (empty($matches)) {
            include_once __DIR__ . '/../../lang/lang_en.php';
            $lang = "en";
        } else {
            foreach ($matches as $m) {
                if ($m[0] == "en") {
                    include_once __DIR__ . '/../../lang/lang_en.php';
                    $this->lang = "en";
                    break;
                } else if ($m[0] == "fr") {
                    include_once __DIR__ . '/../../lang/lang_fr.php';
                    $this->lang = "fr";
                    break;
                } else if ($m[0] == "de") {
                    include_once __DIR__ . '/../../lang/lang_de.php';
                    $this->lang = "de";
                    break;
                }
            }
        }
    }

    public function getLANG()
    {
        return isset($this->lang) ? $this->lang : "en";
    }
}