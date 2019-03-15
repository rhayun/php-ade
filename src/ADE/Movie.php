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
        $this->getCrawler();
    }

    public function getTitle()
    {
        if (null === $this->title) {
            try {
                $this->title = trim($this->crawler->filter('h1')->text());
            } catch (\Exception $e) {
                return null;
            }
        }

        return $this->title;
    }

    public function getRuntime()
    {
       try {
            $runtime = $this->crawler->filterXpath("//small[contains(string(.), 'Length')]/following-sibling::text()[1]")->text();
            return $runtime ? $runtime : NULL;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getReleaseDate()
    {
        try {
            $date = $this->crawler->filterXpath("//small[contains(string(.), 'Released')]/following-sibling::text()[1]")->text();
            return $date ? trim($date) : NULL;
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
            $year = $this->crawler->filterXpath("//small[contains(string(.), 'Production Year')]/following-sibling::text()[1]")->text();
            return $year ? trim($year) : NULL;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getSummary()
    {
        try {
						$summary = '';
						$this->crawler->filterXpath("//h4[contains(concat(' ',normalize-space(@class),' '),' synopsis ')]")->each(function ($node, $i) use (&$summary) {
							$summary .= trim(strip_tags($node->nextSibling->nodeValue));
						});
            //$summary = htmlentities($this->getCrawler()->filterXpath("//h4[@class='spacing-bottom text-white synopsis']//p")->text());
            return trim(html_entity_decode($summary));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getGenres()
    {
        $genres = array();

        try {
            $this->crawler->filterXpath("//a[@label='Category']")->each(function ($node, $i) use (&$genres) {
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
            $this->crawler->filterXpath("//a[@label='Performers - detail']")->each(function ($node, $i) use (&$cast) {
                $cast[] = trim(strip_tags($node->nodeValue));
            });
        } catch (\Exception $e) {
        }

        return $cast;
    }

    public function getCover()
    {
        try {
            $cover = $this->crawler->filterXpath("//a[@id='front-cover']")->attr('data-href');
        } catch (\Exception $e) {}

        return $cover ? $cover : NULL;
    }

    public function getScreens()
    {
        $screens = array();

        try {
            $this->crawler->filterXpath("//a[@rel='scenescreenshots']/@href")->each(function ($node, $i) use (&$screens) {
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
