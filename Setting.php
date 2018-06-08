<?php

/**
 * Line Bot Setting
 * User: Master
 * Date: 2017/5/5
 * Time: 上午 01:41
 * Line Bot setting
 */

//namespace Line;

class Setting
{
    /**
     * @return
     */
    public static function geSetting()
    {
        return array(
            "Setting" => array(
                "LineMessagingAPI" => array(
                    "access_token" => "token",
                    "replyRequest" =>"https://api.line.me/v2/bot/message/reply"
                ),
                "DataBase" =>array(
                    "db_host" =>"localhost",
                    "username"=>"username",
                    "password"=>"pwd",
                    "db_name" =>"iammaste_BOT"
                )
            )
        );
    }
}
