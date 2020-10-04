<?php

namespace app\api\controller\v1;

class User
{
    public function index()
    {
        $arr = array([
            "Image"=> "https://img1.doubanio.com/view/photo/s_ratio_poster/public/p2616740389.jpg",
            "导演" => "徐展雄",
            "编剧" => "徐展雄",
            "主演" => "马思纯 / 钟楚曦 / 黄景瑜 / 王砚辉 / 王阳明",
            "类型" => "剧情 / 爱情",
            "制片国家/地区" => "中国大陆",
            "语言" => "汉语普通话 / 上海话",
            "上映日期" => "2020-08-25(中国大陆) / 2020-07-27(上海电影节)",
            "片长" => "112分钟",
            "又名" => "Wild Grass",
            "IMDb链接" => "tt10080092"
        ],
        [
            "Image" => "https://img1.doubanio.com/view/photo/s_ratio_poster/public/p2616740389.jpg",
            "导演" => "徐展雄",
            "编剧" => "徐展雄",
            "主演" => "马思纯 / 钟楚曦 / 黄景瑜 / 王砚辉 / 王阳明",
            "类型" => "剧情 / 爱情",
            "制片国家/地区" => "中国大陆",
            "语言" => "汉语普通话 / 上海话",
            "上映日期" => "2020-08-25(中国大陆) / 2020-07-27(上海电影节)",
            "片长" => "112分钟",
            "又名" => "Wild Grass",
            "IMDb链接" => "tt10080092"
        ]
    );
        return json_encode($arr);
    } 
}