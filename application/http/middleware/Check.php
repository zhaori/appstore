<?php

namespace app\http\middleware;
use think\facade\Session;

class Check
{
    public function handle($request, \Closure $next)
    {
        $login_user = $request->param("user_name");
        if(Session::get("user_name", "think")!= $login_user){
            return redirect("/user/user/login");
        }
        return $next($request);
    }
}
