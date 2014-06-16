<?php
class Mirror extends Eloquent {

    protected $table = 'mirrors';
    protected $fillable = ['anime_id', 'episode', 'src', 'host', 'subbed', 'quality'];

    public static function add_mirror($anime_id, $episodes) {
        $txt = '';
        if (is_array($episodes)) {
            foreach ($episodes as $episode) {
                $ep = $episode["episode"];
                foreach ($episode["mirrors"] as $mirrors) {
                    foreach ($mirrors as $mirror) {
                        if (isset($mirror["src"][0])) {
                            $host = Mirror::getHost($mirror["src"][0]);
                            if ($host == "failed") {
                                $txt .= '<p class="text-error">Episode '.$ep.' - '.$host.' - Quality'.$mirror["quality"].': <strong>host not found</strong>.</p>';
                            } else if ($mirror["subbed"] == 0) {
                                $txt .= '<p class="text-error">Episode '.$ep.' - '.$host.' - Quality'.$mirror["quality"].': is not <strong>subbed</strong>.</p>';
                            } else {
                                $exists = Mirror::mirror_exsists($anime_id, $ep, $host, $mirror["src"][0]);
                                if (!$exists) {
                                    Mirror::create([
                                        "anime_id" => $anime_id,
                                        "episode" => $ep,
                                        "src" => $mirror["src"][0],
                                        "host" => $host,
                                        "quality" => $mirror["quality"],
                                        "subbed" => $mirror["subbed"]
                                    ]);
                                    $txt .= '<p class="text-success">Episode '.$ep.' - '.$host.' - Quality'.$mirror["quality"].': has been <strong>added</strong>.</p>';
                                } else {
                                    $txt .= '<p class="text-info">Episode '.$ep.' - '.$host.' - Quality'.$mirror["quality"].': this mirror already exists in our database!</p>';
                                }
                            }
                        }
                    }
                }
            }
            return $txt;
        }
        return null;
    }

    public static function delete_mirror($mirrorid) {
        return DB::delete('delete from mirrors where id = ?', array($mirrorid));
    }

    public static function put($animeid) {
        $scraper = new EpisodeScraper($animeid);
        $mirrors = $scraper->get();
        if (!empty($mirrors)) {
            $txt = Mirror::add_mirror($animeid, $mirrors);
            return '<div class="span12"><p style="text-align: center;">Succes! Mirrors have been updated!</p><hr>'.$txt.'</div>';
        }
        return '<div class="span12" style="text-align: center;"><p>Failed! We could not find any mirrors!</p></div>';
    }

    public static function getHost($link) {
        if (strlen($link) > 0) {
            if (strpos($link, "auengine.com") !== false) {
                return "AUEngine";
            } else if (strpos($link, "mp4upload.com") !== false) {
                return "MP4Upload";
            } else if (strpos($link, "videodrive.tv") !== false) {
                return "Videodrive";
            }else if (strpos($link, "videonest.net") !== false) {
                return "Videonest";
            } else if (strpos($link, "veevr.com") !== false) {
                return "Veevr";
            } else if (strpos($link, "putlocker.com") !== false) {
                return "Putlocker";
            } else if (strpos($link, "vidbull.com") !== false) {
                return "Vidbull";
            } else if (strpos($link, "arkvid.tv") !== false) {
                return "Arkvid";
            }
        }
        return "failed";
    }

    public static function mirror_exsists($anime_id, $ep, $host, $url){
        $mirrors = Mirror::whereRaw('anime_id = ? and episode = ? and host = ?', array($anime_id, $ep, $host))->get();
        if (!empty($mirrors)) {
            foreach ($mirrors as $mirror) {
                if ($mirror->src == $url)
                    return true;
            }
        }
        return false;
    }

}