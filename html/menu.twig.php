<?php
/**
 * @var array $data
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <!-- Bootstrap CSS-->
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome-->
    <link rel="stylesheet" href="assets/font-awesome/css/font-awesome.min.css">
    <style type="text/css">
        /* ==========================================================================
    Footer
    ========================================================================== */
        footer {
            color: #bcbac1;
        }

        /* Footer Area */
        .ft-widget-area {
            padding-top: 100px;
            padding-bottom: 100px;
        }

        .ft-fixed-area {
            position: relative;
        }

        .ft-fixed-area .reservation-box {
            color: #fff;
            background-color: #f15f2a;
            background-image: url("assets/images/background/ft-res-bg.jpg");
            background-size: cover;
            padding: 20px;
            height: 100%;
        }

        .ft-fixed-area .reservation-wrap {
            border: 1px solid #fff;
            padding: 10px 25px 25px;
            /*position: relative;*/
        }

        .ft-fixed-area .reservation-wrap:before, .ft-fixed-area .reservation-wrap:after {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #fff;
        }

        .ft-fixed-area .reservation-wrap:before {
            top: -5px;
            right: -5px;
        }

        .ft-fixed-area .reservation-wrap:after {
            bottom: -5px;
            left: -5px;
        }

        .ft-fixed-area .reservation-wrap .res-title {
            font-size: 40px;
            text-align: center;
            font-family: 'Rancho', cursive;
        }

        .ft-fixed-area .reservation-wrap .res-date {
            width: 60%;
            float: left;
            overflow: hidden;
        }

        .ft-fixed-area .reservation-wrap .res-date .res-date-item {
            display: table;
            width: 100%;
        }

        .ft-fixed-area .reservation-wrap .res-date .res-date-text {
            display: table-cell;
        }

        .ft-fixed-area .reservation-wrap .res-date .res-date-dot {
            display: table-cell;
            padding-left: 5px;
            opacity: 0.7;
            font-size: 12px;
            letter-spacing: 1px;
        }

        .ft-fixed-area .reservation-wrap .res-time {
            width: 20%;
            float: right;
            padding-left: 5px;
            color: #1d1b20;
        }

        .ft-fixed-area .reservation-wrap .res-number {
            font-size: 30px;
            font-weight: 700;
            color: #1d1b20;
            text-align: center;
        }

        .res-date-time {
            border-bottom: 1px dotted #fcfcfc;
            margin-bottom: 15px;
        }

        h3 {
            margin-bottom: 20px;
        }

        .wrapper {
            padding: 0;
            width: 100%;
            height: 100%;
            position: fixed;
        }

        .top-title {
            margin-bottom: 35px;
        }

        .ft-fixed-area {
            height: 100%;
        }

        .col-sm-1 {
            float: left;
        }

        code {
            padding: 2px 4px;
            /* font-size: 90%; */
            color: #ffffff;
            /* background-color: #f9f2f4; */
            background: none;
            border-radius: 4px;
            text-shadow: 0 0 1px #0c0c0c;
        }
    </style>
</head>
<body>
<div class="col-lg-4 wrapper container">
    <div class="ft-fixed-area">
        <div class="reservation-box">
            <div class="reservation-wrap">
                <h3 class="res-title top-title">Special Menu</h3>
                <?php foreach ($data as $item): ?>
                    <div class="res-date-time">
                        <div class="res-date-time-item row">
                            <div class="col-sm-1">
                                #<?= $item[0] ?>
                            </div>
                            <div class="res-date col-sm-2">
                                <div class="res-date-item">
                                    <div class="res-date-text">
                                        <p><?= $item[1] ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="res-time">
                                <div class="res-time-item">
                                    <p><?= $item[2] ?>,000 VND</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <h3 class="res-title">Reservation Numbers</h3>
                <p class="res-number">+84 2835 146 056</p>
                <p>
                <div class="res-date-time"></div>

                    <code>
                        /order #số <br/>
                        /order #số [ghi chú khác]
                    </code>
                </p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
