<?php

namespace App\Console\Commands;

use App\Rss;
use App\RssResource;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use UncleNine\FetchRss;

class SpiderRss extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider:rss';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rss信息采集';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $rss_list = RssResource::where('status',true)->get();
        foreach ($rss_list as $item){
            try{
                $rss = (new FetchRss())->parse($item->url);

                //$rss = \Feed::load($item->url);
                $articles = $rss->items;
            }catch (\Exception $exception){
                print 'Line:'.$exception->getLine().' Message:'.$exception->getMessage()."\r\n";
                continue;
            }
            $length = count($articles);
            print "Start Fetch $item->url;\r\n";
            print "Start Insert Db,Count($length)\r\n";

            foreach ($articles as $article){
                if($article['title'] && $article['link']){

                    $title = mb_convert_encoding($article['title'],'utf-8');
                    $url   = mb_convert_encoding($article['link'],'utf-8');
                    Rss::firstOrCreate(['title'=>$title,'url'=>$url],[
                        'title'     => trim($title),
                        'summary'   => mb_convert_encoding($article['description'],'utf-8'),
                        'url'       => trim($url),
                        'pub_date'  => date('Y-m-d H:i:s',strtotime(mb_convert_encoding($article['pubdate'] ?? date('Y-m-d H:i:s'),'utf-8'))),
                    ]);
                }
            }
        }
    }
}
