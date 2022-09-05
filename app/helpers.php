<?php


function isAdminRoute(){
    return request()->is('admin/*') ? true : false ;
}
