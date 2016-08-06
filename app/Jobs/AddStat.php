<?php

   namespace App\Jobs;

   use Illuminate\Contracts\Queue\ShouldQueue;
   use Illuminate\Queue\InteractsWithQueue;
   use Illuminate\Queue\SerializesModels;
   use Illuminate\Support\Facades\Redis;
   use PhpParser\Parser;
   use Scriptixru\SypexGeo\SypexGeoFacade;

   class AddStat extends Job implements ShouldQueue
   {
      use InteractsWithQueue, SerializesModels;

      //Константы префиксов имен объектов и полей
      const PAGE_PREF='pg';      // Страница
      const BROWSER_PREF='br';   // Браузер
      const OS_PREF='os';        // ОС
      const GEO_PREF='ge';       // Гео
      const REF_PREF='re';       // РЕФ
      const URL_PREF='ul';       // ССЫЛКА
      const HIT_PREF='hi';       // Хит
      const UQ_IP_PREF='ui';     // Уник по IP
      const UQ_COOKIE_PREF='uc'; // Уник по куки
      const NAME_PREF='nm';      // НАИМЕНОВАНИЕ

      private $browser;
      private $os;
      private $ref;
      private $geo;
      private $ip;
      private $cookie;
      private $url;
      private $data;
      private $inc_ip=false;
      private $inc_cookie=false;
      private $is_new_id;

      private $page_id;
      private $browser_id;
      private $os_id;
      private $geo_id;
      private $ref_id;
      private $site_id=0;
      private $ip_id;
      private $cookie_id;


      /**
       * @var \Predis\ClientInterface
       */
      private $r;

      /**
       * Create a new job instance.
       *
       * @param $data array
       */
      public function __construct($data)
      {
         $this->r=Redis::connection();
         $this->data=$data;
      }

      protected function getId($t, $f, $v)
      {
         $this->is_new_id = false;
         $key = $t . ':' . $f . ':' . $v;
         if( $this->r->exists($key) )
            return $this->r->get($key);
         $id = $this->r->incr($t.':id');
         $this->r->set($key,$id);
         $this->is_new_id = true;
         return $id;
      }

      protected function parseData()
      {
         $this->url=$this->data[ 'url' ];
         $this->cookie=$this->data[ 'cookie' ];
         $this->ip=$this->data[ 'ip' ];
         // Парс UserAgent
         $parser=\UAParser\Parser::create();
         $res=$parser->parse($this->data[ 'HTTP_USER_AGENT' ]);
         $this->browser=str_replace(' ','_',$res->ua->family).'_'.$res->ua->major.'.'.$res->ua->minor;;
         $this->os=str_replace(' ','_',$res->os->family).'_'.$res->os->major.'.'.$res->os->minor;
         //Парс geo
         $geo = \SypexGeo::get($this->ip);
         if (isset($geo))
            $this->geo = $geo['country']['iso'];
         // Парс ref
         if (isset($this->data[ 'HTTP_REFERER' ]))
           $this->ref=parse_url($this->data[ 'HTTP_REFERER' ], PHP_URL_HOST);
         else
            $this->ref = 'direct link';
         $this->page_id=$this->getId(self::PAGE_PREF, self::URL_PREF, $this->url);
         $this->browser_id=$this->getId(self::BROWSER_PREF, self::NAME_PREF, $this->browser);
         $this->os_id=$this->getId(self::OS_PREF, self::NAME_PREF, $this->os);
         $this->geo_id=$this->getId(self::GEO_PREF, self::NAME_PREF, $this->geo);
         $this->ref_id=$this->getId(self::REF_PREF, self::URL_PREF, $this->ref);
         $this->ip_id=$this->getId(self::UQ_IP_PREF, self::NAME_PREF, $this->ip);
         $this->inc_ip = $this->is_new_id;
         $this->cookie_id=$this->getId(self::UQ_COOKIE_PREF, self::NAME_PREF, $this->cookie);
         $this->inc_cookie = $this->is_new_id;
      }


      /**
       * Execute the job.
       *
       * @return void
       */
      public function handle()
      {
         $this->parseData();
// по сайту
         //Total
         $this->r->zadd(self::PAGE_PREF, [$this->url=>$this->page_id]); // pages
         $this->r->hincrby(self::PAGE_PREF.':'. $this->site_id, self::HIT_PREF, 1); // total hits

         //Browser
         $key = self::PAGE_PREF.':'. $this->site_id.':'.self::BROWSER_PREF;
         $this->r->zadd($key,[$this->browser=>$this->browser_id]);
         $key .= ':'. $this->browser_id;
         $this->r->hincrby($key, self::HIT_PREF, 1);
         $this->r->hset($key, self::NAME_PREF,$this->browser);

         //OS
         $key = self::PAGE_PREF.':'. $this->site_id.':' .self::OS_PREF;
         $this->r->zadd($key,[$this->os=>$this->os_id]);
         $key .=':'. $this->os_id;
         $this->r->hincrby($key, self::HIT_PREF, 1);
         $this->r->hset($key, self::NAME_PREF,$this->os);

         //GEO
         $key = self::PAGE_PREF.':'. $this->site_id.':'.self::GEO_PREF;
         $this->r->zadd($key,[$this->geo=>$this->geo_id]);
         $key .= ':'. $this->geo_id;
         $this->r->hincrby($key, self::HIT_PREF, 1);
         $this->r->hset($key, self::NAME_PREF ,$this->geo);

         //REF
         $key = self::PAGE_PREF.':'. $this->site_id.':'.self::REF_PREF;
         $this->r->zadd($key,[$this->ref=>$this->ref_id]);
         $key .= ':'. $this->ref_id;
         $this->r->hincrby($key, self::HIT_PREF, 1);
         $this->r->hset($key, self::NAME_PREF,$this->ref);

// по странице
         //Total
         $this->r->hincrby(self::PAGE_PREF.':'. $this->page_id, self::HIT_PREF, 1);
         $this->r->hset(self::PAGE_PREF.':'. $this->page_id, self::URL_PREF, $this->url);

         //Browser
         $this->r->zadd(self::PAGE_PREF.':'. $this->page_id.':'.self::BROWSER_PREF,[$this->browser=>$this->browser_id]);
         $this->r->hincrby(self::PAGE_PREF.':'. $this->page_id .':'.self::BROWSER_PREF.':'. $this->browser_id, self::HIT_PREF, 1);

         //OS
         $this->r->zadd(self::PAGE_PREF.':'. $this->page_id.':'.self::OS_PREF,[$this->os=>$this->os_id]);
         $this->r->hincrby(self::PAGE_PREF.':'. $this->page_id .':'.self::OS_PREF.':'. $this->os_id, self::HIT_PREF, 1);

         //GEO
         $this->r->zadd(self::PAGE_PREF.':'. $this->page_id.':'.self::GEO_PREF,[$this->geo=>$this->geo_id]);
         $this->r->hincrby(self::PAGE_PREF.':'. $this->page_id .':'.self::GEO_PREF.':'. $this->geo_id, self::HIT_PREF, 1);

         //REF
         $this->r->zadd(self::PAGE_PREF.':'. $this->page_id.':'.self::REF_PREF,[$this->ref=>$this->ref_id]);
         $this->r->hincrby(self::PAGE_PREF.':'. $this->page_id .':'.self::REF_PREF.':'. $this->ref_id, self::HIT_PREF, 1);

         if($this->inc_ip){  // уники по IP
            // по сайту
            //Total
            $this->r->hincrby(self::PAGE_PREF.':'. $this->site_id, self::UQ_IP_PREF, 1);
            //Browser
            $this->r->hincrby(self::PAGE_PREF.':'. $this->site_id.':'.self::BROWSER_PREF.':'. $this->browser_id, self::UQ_IP_PREF, 1);
            //OS
            $this->r->hincrby(self::PAGE_PREF.':'. $this->site_id.':' .self::OS_PREF.':'. $this->os_id, self::UQ_IP_PREF, 1);
            //GEO
            $this->r->hincrby(self::PAGE_PREF.':'. $this->site_id.':'.self::GEO_PREF.':'. $this->geo_id, self::UQ_IP_PREF, 1);
            //REF
            $this->r->hincrby(self::PAGE_PREF.':'. $this->site_id.':'.self::REF_PREF.':'. $this->ref_id, self::UQ_IP_PREF, 1);
            $this->r->hset($key, self::NAME_PREF,$this->ref);

            // по странице
            //Total
            $this->r->hincrby(self::PAGE_PREF.':'. $this->page_id, self::UQ_IP_PREF, 1);
            //Browser
            $this->r->hincrby(self::PAGE_PREF.':'. $this->page_id .':'.self::BROWSER_PREF.':'. $this->browser_id, self::UQ_IP_PREF, 1);
            //OS
            $this->r->hincrby(self::PAGE_PREF.':'. $this->page_id .':'.self::OS_PREF.':'. $this->os_id, self::UQ_IP_PREF, 1);
            //GEO
            $this->r->hincrby(self::PAGE_PREF.':'. $this->page_id .':'.self::GEO_PREF.':'. $this->geo_id, self::UQ_IP_PREF, 1);
            //REF
            $this->r->hincrby(self::PAGE_PREF.':'. $this->page_id .':'.self::REF_PREF.':'. $this->ref_id, self::UQ_IP_PREF, 1);
         }
         if($this->inc_cookie){  // уники по cookie
            // по сайту
            //Total
            $this->r->hincrby(self::PAGE_PREF.':'. $this->site_id, self::UQ_COOKIE_PREF, 1);
            //Browser
            $this->r->hincrby(self::PAGE_PREF.':'. $this->site_id.':'.self::BROWSER_PREF.':'. $this->browser_id, self::UQ_COOKIE_PREF, 1);
            //OS
            $this->r->hincrby(self::PAGE_PREF.':'. $this->site_id.':' .self::OS_PREF.':'. $this->os_id, self::UQ_COOKIE_PREF, 1);
            //GEO
            $this->r->hincrby(self::PAGE_PREF.':'. $this->site_id.':'.self::GEO_PREF.':'. $this->geo_id, self::UQ_COOKIE_PREF, 1);
            //REF
            $this->r->hincrby(self::PAGE_PREF.':'. $this->site_id.':'.self::REF_PREF.':'. $this->ref_id, self::UQ_COOKIE_PREF, 1);
            $this->r->hset($key, self::NAME_PREF,$this->ref);

            // по странице
            //Total
            $this->r->hincrby(self::PAGE_PREF.':'. $this->page_id, self::UQ_COOKIE_PREF, 1);
            //Browser
            $this->r->hincrby(self::PAGE_PREF.':'. $this->page_id .':'.self::BROWSER_PREF.':'. $this->browser_id, self::UQ_COOKIE_PREF, 1);
            //OS
            $this->r->hincrby(self::PAGE_PREF.':'. $this->page_id .':'.self::OS_PREF.':'. $this->os_id, self::UQ_COOKIE_PREF, 1);
            //GEO
            $this->r->hincrby(self::PAGE_PREF.':'. $this->page_id .':'.self::GEO_PREF.':'. $this->geo_id, self::UQ_COOKIE_PREF, 1);
            //REF
            $this->r->hincrby(self::PAGE_PREF.':'. $this->page_id .':'.self::REF_PREF.':'. $this->ref_id, self::UQ_COOKIE_PREF, 1);
         }
         
      }
   }
