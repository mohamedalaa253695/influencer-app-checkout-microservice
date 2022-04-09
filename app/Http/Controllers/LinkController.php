<?php
namespace App\Http\Controllers;

use App\Models\Link;
use InfluencerMicroservices\UserService;

class LinkController extends Controller
{
    public UserService $userService;

    public function __construct(UserService $userService)
    {
        // dd('linkcontroller');
        $this->userService = $userService;
    }

    public function show($code)
    {
        // dd($code);
        $link = Link::with('products')->where('code', $code)->first();
        // $link = Link::all();
        // return($link->user_id);
        $user = $this->userService->get($link->user_id);
        // return($user);
        $link['user'] = $user;

        return $link;
    }
}
