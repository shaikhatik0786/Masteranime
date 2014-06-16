<?php
use Goutte\Client;

class EpisodeScraper
{

    protected $animerush_base_url = "http://www.animerush.tv/";
    protected $rawranime_base_url = "http://rawranime.tv/";
    protected $base_filter_ra = "div#rawr_wrapper > div#ipbwrapper > div#content.clearfix";
    protected $client;
    protected $mirrors;
    public $anime_id;

    public function __construct($anime_id)
    {
        $this->anime_id = $anime_id;
        $this->client = new Client();
    }

    private function scrape_ar($suffix, $startep = 1)
    {
        $url = $this->animerush_base_url . $suffix . '-episode-' . $startep;
        $crawler = $this->client->request('GET', $url);
        while (count($crawler->filter('div.ep-next > a')) > 0) {
            $episode = $crawler->filter('div.bannerep > div.bannertit > h1')->extract('_text')[0];
            $episode = filter_var(explode(' Episode ', $episode)[1], FILTER_SANITIZE_NUMBER_FLOAT);
            $mirrors = $crawler->filter('div#left-column > div#episodes')->each(function (\Symfony\Component\DomCrawler\Crawler $node) {
                $links =  $node->filter('div.episode1 > div > div > h3 > a')->links();
                $mirrors = array();
                foreach ($links as $link) {
                    $crawler = $this->client->click($link);
                    $quality = 480;
                    if (count($crawler->filter('div.episode1 > div.episode_on > div > div.hdlogo')) > 0) {
                        $quality = 720;
                    }
                    $subbed = false;
                    if (count($crawler->filter('div.episode1 > div.episode_on > div > span.mirror-sub.subbed')) > 0) {
                        $subbed = true;
                    }
                    $src = $crawler->filter('div#left-column > div.player-area > div > div > iframe')->first()->extract('src');
                    array_push($mirrors, array( "quality" => $quality, "subbed" => $subbed, "src" => $src));
                }
                return $mirrors;
            });
            if (!empty($mirrors) && !empty($episode)) {
                array_push($this->mirrors, array(
                    "episode" => $episode,
                    "mirrors" => $mirrors
                ));
            }
            $link = $crawler->filter('div.ep-next > a')->link();
            $crawler = $this->client->click($link);
        }
        return $this->mirrors;
    }

    private function get_mirrors_ra($details_array) {
        $mirrors = array();
        $count = 0;
        foreach ($details_array[0]["mirror_ids"] as $id) {
            $url = 'http://rawranime.tv/index.php?app=anime&module=ajax&section=anime_watch_handler&md5check=880ea6a14ea49e853634fbdc5015a024&do=getvid&id='.$id;
            $crawler = $this->client->request('GET', $url);
            $src = $crawler->filter('body > iframe')->extract('src')[0];
            switch ($details_array[0]["details"][($count * 2)]) {
                case 'subbed_trait':
                    $subbed = 1;
                    break;
                default:
                    $subbed = 0;
                    break;
            }
            switch ($details_array[0]["details"][($count * 2)+1]) {
                case 'hd_1080p_trait':
                    $quality = 1080;
                    break;
                case 'hd_720p_trait':
                    $quality = 720;
                    break;
                default:
                    $quality = 480;
                    break;
            }
            array_push($mirrors, array( "quality" => $quality, "subbed" => $subbed, "src" => $src));
            $count++;
        }
        return $mirrors;
    }

    private function scrape_ra($suffix, $startep = 1) {
        $url = $this->rawranime_base_url . $suffix . $startep;
        $crawler = $this->client->request('GET', $url);
        while (count($crawler->filter($this->base_filter_ra . ' > div#nav_bar.nav_crumb_center > div#episode_nav > a > div#t_next')) > 0) {
            $episode = $crawler->filter($this->base_filter_ra . ' > div#nav_bar.nav_crumb_center > div#episode_nav > div#episode_header > div#episode_title')->extract('_text')[0];
            $episode = explode('Episode ', $episode);
            if (count($episode) > 1) {
                $episode = filter_var($episode[1], FILTER_SANITIZE_NUMBER_FLOAT);
            } else {
                $episode = filter_var($episode[0], FILTER_SANITIZE_NUMBER_FLOAT);
            }
            $mirror_details = $crawler->filter($this->base_filter_ra . ' > div#sidebar > div#mirror_container > if')->each(function (\Symfony\Component\DomCrawler\Crawler $node) {
                $details = $node->filter('div.mirror > div.mirror_traits > div')->extract('class');
                $ids = $node->filter('div.mirror')->extract('rn');
                return array(
                    "details" => $details,
                    "mirror_ids" => $ids
                );
            });
            $mirrors = $this->get_mirrors_ra($mirror_details);
            if (!empty($mirrors) && !empty($episode)) {
                array_push($this->mirrors, array(
                    "episode" => $episode,
                    "mirrors" => $mirrors
                ));
            }
            $link = $crawler->filter($this->base_filter_ra . ' > div#nav_bar.nav_crumb_center > div#episode_nav > a')->last()->link();
            $crawler = $this->client->click($link);
        }
        return $this->mirrors;
    }

    public function get($anime_platform = AnimePlatform::all)
    {
        $this->mirrors = array();
        $anime = Anime::find($this->anime_id);
        if (empty($anime)) {
            return null;
        }
        $urls = ScrapeUrl::find($this->anime_id);
        if (empty($urls)) {
            return $this->scrape_ar($anime->hum_id);
        } else {
            if ($anime_platform == AnimePlatform::all) {
                if (empty($urls->suffix_animerush)) {
                    $this->scrape_ar($anime->hum_id);
                } else {
                    $this->scrape_ar($urls->suffix_animerush);
                }
                if (!empty($urls->suffix_rawranime)) {
                    $this->scrape_ra($urls->suffx_rawranime);
                }
                return $this->mirrors;
            } else if ($anime_platform == AnimePlatform::animerush) {
                if (empty($urls->suffix_animerush)) {
                    return $this->scrape_ar($anime->hum_id);
                }
                return $this->scrape_ar($urls->suffix_animerush);
            } else if ($anime_platform == AnimePlatform::rawranime) {
                if (!empty($urls->suffix_rawranime)) {
                    return $this->scrape_ra($urls->suffx_rawranime);
                }
                return null;
            }
        }
        return null;
    }

}

abstract class AnimePlatform {

    const all = 0;
    const animerush = 1;
    const rawranime = 2;

}