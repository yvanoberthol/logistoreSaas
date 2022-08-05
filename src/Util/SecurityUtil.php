<?php


namespace App\Util;


use Symfony\Component\Routing\RouterInterface;

final class SecurityUtil
{

    public static function randomString($size): string
    {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomString = '';

            for ($i = 0; $i < $size; $i++) {
                $index = rand(0, strlen($characters) - 1);
                $randomString .= $characters[$index];
            }

            return $randomString;
    }

    public static function getPermissions(RouterInterface $router): array
    {
        $subPaths = [
            'imageLogo',
            'titleStore',
            'linkStore',
            'notice',
            'nbProduct',
            'account_login',
            'documentation',
            'installation',
        ];

        $allRoutes = $router->getRouteCollection()->all();

        $routeNames = [];
        foreach ($allRoutes as $key=>$value){
            if (!str_contains($key,'profiler') && !str_starts_with($key,'rest')
                && !str_starts_with($key,'test') && !str_starts_with($key,'_') && !in_array($key,$subPaths,true))
                $routeNames[] = $key;
        }

        return $routeNames;
    }

}