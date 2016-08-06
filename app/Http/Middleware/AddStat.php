<?php

   namespace App\Http\Middleware;

   use Closure;
   use Cookie;
   use Queue;

   class AddStat
   {
      /**
       * Handle an incoming request.
       *
       * @param  \Illuminate\Http\Request $request
       * @param  \Closure                 $next
       *
       * @return mixed
       */
      public function handle($request, Closure $next)
      {
         if( preg_match("/\A\/admin\/stat/", $_SERVER[ 'REQUEST_URI' ]) === 1 )
            return $next($request);
         $data=array_only(
            $_SERVER, [
            'HTTP_USER_AGENT',
            'HTTP_REFERER' ]);
         $data[ 'url' ]=\URL::full();
         $data[ 'ip' ]=$request->getClientIp();
         if( ( $uq=$request->cookie('uniq', null) ) === null )
            $uq=uniqid('cl');
         Cookie::queue('uniq', $uq, 10512000);
         $data[ 'cookie' ]=$uq;

         Queue::push(new \App\Jobs\AddStat($data));
         return $next($request);
      }
   }
