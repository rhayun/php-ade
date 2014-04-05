<?php

namespace ADE;

use Buzz\Browser;
use Symfony\Component\DomCrawler\Crawler;

class Movie
{
    protected $title;
    protected $url;

    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * Constructor
     *
     * @param $url
     */
    public function __construct($url)
    {
        $this->title = null;
        $this->url   = $url;
    }

    public function getTitle()
    {
        if (null === $this->title) {
            try {
                $this->title = trim($this->getCrawler()->filter('h1')->text());
            } catch (\Exception $e) {
                return null;
            }
        }

        return $this->title;
    }

    public function getRuntime()
    {
       try {
            $runtime = $this->getCrawler()->filterXpath("//strong[contains(string(.), 'Length')]/following-sibling::text()[1]")->text();
            return $runtime ? $runtime : NULL;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getReleaseDate()
    {
        try {
            $date = $this->getCrawler()->filterXpath("//strong[contains(string(.), 'Released')]/following-sibling::text()[1]")->text();
            return $date ? $date : NULL;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getReleaseDateUnixtime()
    {
        try {
            $releaseDate = $this->getReleaseDate();
            return strtotime($releaseDate);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getYear()
    {
        try {
            $year = $this->getCrawler()->filterXpath("//strong[contains(string(.), 'Production Year')]/following-sibling::text()[1]")->text();
            return $year ? $year : NULL;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getSummary()
    {
        try {
            $summary = '';
            $this->getCrawler()->filterXpath("//div[@class='Section Synopsis']//p")->each(function ($node, $i) use (&$summary) {
                $summary .= htmlentities($node->nodeValue);
            });
            return trim(html_entity_decode($summary));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getGenres()
    {
        $genres = array();

        try {
            $this->getCrawler()->filterXpath("//div[@class='Section Categories']/p/a")->each(function ($node, $i) use (&$genres) {
                $genres[] = trim(strip_tags($node->nodeValue));
            });
        } catch (\Exception $e) {
        }

        return $genres;
    }

    public function getCast()
    {
        $cast = array();

        try {
            $this->getCrawler()->filterXpath("//div[@class='Section Cast']/ul/li/a[@class='PerformerName']")->each(function ($node, $i) use (&$cast) {
                $cast[] = trim(strip_tags($node->nodeValue));
            });
        } catch (\Exception $e) {
        }

        return $cast;
    }

    public function getScreens()
    {
        $scene_url = str_replace('.com', '.com/scenes', $this->url);

        $screens = array();

        try {
            $this->getCrawler($scene_url)->filterXpath("//div[@id='scene']/a[@rel='screenshots']/@href")->each(function ($node, $i) use (&$screens) {
                $screens[] = trim($node->nodeValue);
            });
        } catch (\Exception $e) {

        }

        return !empty($screens) ? $screens : array();
    }

    /**
     * @return Crawler
     */
    protected function getCrawler($url = NULL)
    {
        if (null === $this->crawler || $url) {
            if(!$url) {
                $url = $this->url;
            }

            $client = new Browser();

            $this->crawler = new Crawler($client->get($url, array('User-Agent:MyAgent/1.0\r\n'))->getContent());
        }

        return $this->crawler; 
    }
}
