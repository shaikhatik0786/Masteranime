<?php
class MasterAnime {

    public static function getEpisodes($id) {
        $episodes = array();
        foreach (Mirror::where('anime_id', '=', $id)->orderBy(DB::raw('CAST(episode AS SIGNED)'), 'DESC')->get() as $mirror) {
            if (empty($episodes)) {
                array_push($episodes, $mirror->episode);
            } else {
                $found = false;
                foreach ($episodes as $episode) {
                    if ($episode == $mirror->episode)
                        $found = true;
                }
                if (!$found)
                    array_push($episodes, $mirror->episode);
            }
        }
        return $episodes;
    }

    public static function getEpisode($id, $episode) {
        return Mirror::whereRaw('anime_id = ? and episode = ?', array($id, $episode))->orderBy('quality', 'DESC')->orderBy(DB::raw("field(host, 'MP4Upload','Arkvid', 'Masteranime') "), 'DESC')->get();
    }

}