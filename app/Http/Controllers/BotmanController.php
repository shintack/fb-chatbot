<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Facebook\Extensions\ButtonTemplate;
use BotMan\Drivers\Facebook\Extensions\ElementButton;
use BotMan\Drivers\Facebook\Extensions\GenericTemplate;
use BotMan\Drivers\Facebook\Extensions\Element;
use BotMan\Drivers\Facebook\Extensions\ListTemplate;
use BotMan\Drivers\Facebook\Extensions\ReceiptAddress;
use BotMan\Drivers\Facebook\Extensions\ReceiptAdjustment;
use BotMan\Drivers\Facebook\Extensions\ReceiptElement;
use BotMan\Drivers\Facebook\Extensions\ReceiptSummary;
use BotMan\Drivers\Facebook\Extensions\ReceiptTemplate;
use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;

// use BotMan\BotMan\Cache\RedisCache;

use BotMan\BotMan\Cache\SymfonyCache;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

use Illuminate\Support\Facades\Storage;

class BotmanController extends Controller
{
    private $config;

    public function __construct()
    {
        $this->config = [
            // Your driver-specific configuration
            // "telegram" => [
            //    "token" => "TOKEN"
            // ]
            'facebook' => [
                'token' => 'EAAJG3TTxzqMBAAZBd2yHRkg7ZChZCQNuPKq6UZBrwgnSTyZAvsSaKcTmD9wZAVur8ZAUjHVuZBIJ9O6grUNmycmVXf7aSGZBcZALrNmsa08b1AeiphvJZBNSwz7xAs1mwPQ2X0gzwrHlKM5V6ZCr4VYlsFhJmHFtFd3SZC1KEMizSPfGwEr2k16m3jyez',
                'app_secret' => 'cf4b0e4b62ed832717a1d7747fe8a1ee',
                'verification' => '12345678abc',
            ]
        ];
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id = null)
    {
        $file = Storage::disk('local')->get('data/pageToken.json');

        $collectData = collect(json_decode($file));

        $conf = $collectData->filter(function ($item, $key) use ($id) {
            return $key == $id;
        })->first();

        $this->config = [
            'facebook' => [
                'token' => $conf->facebook->token,
                'app_secret' => $conf->facebook->app_secret,
                'verification' => $conf->facebook->verification,
            ]
        ];

        // Load the driver(s) you want to use
        DriverManager::loadDriver(\BotMan\Drivers\Facebook\FacebookDriver::class);

        // Create an instance
        $adapter = new FilesystemAdapter();
        $botman = BotManFactory::create($this->config, new SymfonyCache($adapter));

        // Give the bot something to listen for.
        $botman->hears('GET_STARTED', function (BotMan $bot) {
            $bot->reply('Hello ada yang bisa kami bantu?.');
            $bot->reply(
                ButtonTemplate::create('Silahkan gunakan menu ini')
                    ->addButton(
                        ElementButton::create('Produk rekomendasi')
                            ->type('postback')
                            ->payload('recomendation')
                    )
                    ->addButton(
                        ElementButton::create('Bicara dengan agen')
                            ->type('postback')
                            ->payload('talk-to-agen')
                    )
            );
        });

        $botman->hears('hello', function (BotMan $bot) {
            $bot->reply('Hello ada yang bisa kami bantu?.');
            $bot->reply(
                ButtonTemplate::create('Silahkan gunakan menu ini')
                    ->addButton(
                        ElementButton::create('Produk rekomendasi')
                            ->type('postback')
                            ->payload('recomendation')
                    )
                    ->addButton(
                        ElementButton::create('Bicara dengan agen')
                            ->type('postback')
                            ->payload('talk-to-agen')
                    )
            );
        });

        $botman->hears('tellmemore', function (BotMan $bot) {
            $bot->reply(
                GenericTemplate::create()
                    ->addImageAspectRatio(GenericTemplate::RATIO_SQUARE)
                    ->addElements([
                        Element::create('BotMan Documentation')
                            ->subtitle('All about BotMan')
                            ->image('http://botman.io/img/botman-body.png')
                            ->addButton(
                                ElementButton::create('visit')
                                    ->url('http://botman.io')
                            )
                            ->addButton(
                                ElementButton::create('tell me more')
                                    ->payload('tellmemore2')
                                    ->type('postback')
                            ),
                        Element::create('BotMan Laravel Starter')
                            ->subtitle('This is the best way to start with Laravel and BotMan')
                            ->image('http://botman.io/img/botman-body.png')
                            ->addButton(
                                ElementButton::create('visit')
                                    ->url('https://github.com/mpociot/botman-laravel-starter')
                            ),
                    ])
            );
        });

        $botman->hears('tellmemore2', function (BotMan $bot) {
            /* deprecated
            $bot->reply(
                ListTemplate::create()
                    ->useCompactView()
                    ->addGlobalButton(
                        ElementButton::create('view more')
                            ->url('http://test.at')
                    )
                    ->addElement(
                        Element::create('BotMan Documentation')
                            ->subtitle('All about BotMan')
                            ->image('http://botman.io/img/botman-body.png')
                            ->addButton(
                                ElementButton::create('tell me more')
                                    ->payload('tellmemore')
                                    ->type('postback')
                            )
                    )
                    ->addElement(
                        Element::create('BotMan Laravel Starter')
                            ->subtitle('This is the best way to start with Laravel and BotMan')
                            ->image('http://botman.io/img/botman-body.png')
                            ->addButton(
                                ElementButton::create('visit')
                                    ->url('https://github.com/mpociot/botman-laravel-starter')
                            )
                    )
            );
            */
            $bot->reply(
                ReceiptTemplate::create()
                    ->recipientName('Christoph Rumpel')
                    ->merchantName('BotMan GmbH')
                    ->orderNumber('342343434343')
                    ->timestamp('1428444852')
                    ->orderUrl('http://test.at')
                    ->currency('USD')
                    ->paymentMethod('VISA')
                    ->addElement(
                        ReceiptElement::create('T-Shirt Small')
                            ->price(15.99)
                            ->image('http://botman.io/img/botman-body.png')
                    )
                    ->addElement(
                        ReceiptElement::create('Sticker')
                            ->price(2.99)
                            ->image('http://botman.io/img/botman-body.png')
                    )
                    ->addAddress(
                        ReceiptAddress::create()
                            ->street1('Watsonstreet 12')
                            ->city('Bot City')
                            ->postalCode(100000)
                            ->state('Washington AI')
                            ->country('Botmanland')
                    )
                    ->addSummary(
                        ReceiptSummary::create()
                            ->subtotal(18.98)
                            ->shippingCost(10)
                            ->totalTax(15)
                            ->totalCost(23.98)
                    )
                    ->addAdjustment(
                        ReceiptAdjustment::create('Laravel Bonus')
                            ->amount(5)
                    )
            );
        });

        // Give the bot something to listen for.
        $botman->hears('recomendation', function (BotMan $bot) {
            $bot->reply(
                GenericTemplate::create()
                    ->addImageAspectRatio(GenericTemplate::RATIO_SQUARE)
                    ->addElements([
                        Element::create('BotMan Documentation')
                            ->subtitle('All about BotMan')
                            ->image('https://api.deplaza.id/photo/plugin/shop/2019/1553139887_3410-org.png')
                            ->addButton(
                                ElementButton::create('Tampilkan lainnya')
                                    ->payload('recomendation')
                                    ->type('postback')
                            )
                            ->addButton(
                                ElementButton::create('Tampilkan lainnya')
                                    ->payload('recomendation')
                                    ->type('postback')
                            ),
                        Element::create('BotMan Documentation')
                            ->subtitle('All about BotMan')
                            ->image('https://api.deplaza.id/photo/plugin/shop/2019/1553139887_3410-org.png')
                            ->addButton(
                                ElementButton::create('Tampilkan lainnya')
                                    ->payload('recomendation')
                                    ->type('postback')
                            )
                            ->addButton(
                                ElementButton::create('Tampilkan lainnya')
                                    ->payload('recomendation')
                                    ->type('postback')
                            ),
                    ])
            );
        });

        $botman->fallback(function ($bot) {
            $bot->reply('Maaf, ...');
            $bot->reply(
                ButtonTemplate::create('Silahkan gunakan menu ini')
                    ->addButton(
                        ElementButton::create('Produk rekomendasi')
                            ->type('postback')
                            ->payload('recomendation')
                    )
                    ->addButton(
                        ElementButton::create('Bicara dengan agen')
                            ->type('postback')
                            ->payload('talk-to-agen')
                    )
            );
        });

        // Start listening
        $botman->listen();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
