<?php

   namespace App\Http\Controllers\Admin;

   use App\Http\Controllers\Controller;
   use App\Http\Requests;
   use App\Jobs\AddStat;
   use Illuminate\Support\Facades\Redis;

   class StatController extends Controller
   {

      /**
       * Show the application dashboard.
       *
       * @return \Illuminate\Http\Response
       */
      public function index($pg_id=0)
      {
         /** @var \Predis\ClientInterface $r */
         $r=Redis::connection();
         $pgs=$r->zrange(AddStat::PAGE_PREF, 0, -1, [ 'WITHSCORES'=>true ]);
         $br_key = AddStat::PAGE_PREF.':'.$pg_id.':'.AddStat::BROWSER_PREF;
         $brs=$r->zrange($br_key, 0, -1, [ 'WITHSCORES'=>true ]);
         $os_key = AddStat::PAGE_PREF.':'.$pg_id.':'.AddStat::OS_PREF;
         $oss=$r->zrange($os_key, 0, -1, [ 'WITHSCORES'=>true ]);
         $geo_key = AddStat::PAGE_PREF.':'.$pg_id.':'.AddStat::GEO_PREF;
         $geos=$r->zrange($geo_key, 0, -1, [ 'WITHSCORES'=>true ]);
         $ref_key = AddStat::PAGE_PREF.':'.$pg_id.':'.AddStat::REF_PREF;
         $refs=$r->zrange($ref_key, 0, -1, [ 'WITHSCORES'=>true ]);
         return view('admin/stat', [
            'pages'=>$pgs,
            'pg_key'=>AddStat::PAGE_PREF,
            'browsers'=>$brs,
            'br_key'=>$br_key,
            'oss'=>$oss,
            'os_key'=>$os_key,
            'geos'=>$geos,
            'geo_key'=>$geo_key,
            'refs'=>$refs,
            'ref_key'=>$ref_key,
            'r'=>$r
         ]);
      }
   }
