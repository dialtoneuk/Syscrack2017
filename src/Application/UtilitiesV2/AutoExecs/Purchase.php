<?php
/**
 * Created by PhpStorm.
 * User: lewis
 * Date: 12/08/2018
 * Time: 20:49
 */

namespace Framework\Application\UtilitiesV2\AutoExecs;


use Framework\Application\UtilitiesV2\Balance;
use Framework\Application\UtilitiesV2\Shop;

class Purchase extends Base
{

    /**
     * @var Balance
     */

    protected $balance;

    /**
     * @var Shop
     */

    protected $shop;

    /**
     * Purchase constructor.
     * @throws \RuntimeException
     */

    public function __construct()
    {

        $this->balance = new Balance();
        $this->shop = new Shop();

        parent::__construct();
    }

    /**
     * @param array $data
     * @throws \RuntimeException
     */

    public function execute(array $data)
    {

        $cost = $this->checkData( $data );

        $this->shop->before( $data["item"] );

        if( $this->shop->authenticate( $data["userid"], $data["item"], $data ) == false )
            throw new \RuntimeException("Failed to authenticate purchase. This is probably because you've maxed out your limit on a specific permission value!");

        if( @$this->shop->complete( $data["userid"], $data["item"], $data ) == false )
            throw new \RuntimeException("Failed to complete.");

        $this->shop->createTransaction( $data["userid"], $data["item"], $cost,TRANSACTION_TYPE_WITHDRAW );
        $this->balance->modify( $data["balanceid"], -$cost );
    }

    /**
     * @param $data
     * @return int
     * @throws \RuntimeException
     */

    private function checkData( $data )
    {

        if( isset( $data["userid"] ) == false )
            throw new \RuntimeException("Expecting userid");

        if( isset( $data["balanceid"] ) == false )
            throw new \RuntimeException("Expecting balance id");

        if( isset( $data["item"] ) == false )
            throw new \RuntimeException("Expecting item");

        if( $this->shop->exist( $data["item"] ) == false )
            throw new \RuntimeException("Item does not exist");

        if( $this->shop->hasTransactionItem( $data["userid"], $data["item"] ) )
        {

            $item = $this->shop->getInventoryItem( $data["item"] );

            if( $item["onetime"] == true )
                throw new \RuntimeException("Cannot buy this item more than one time");
        }

        if( $this->balance->exists( $data["balanceid"] ) == false )
            throw new \RuntimeException("Balance ID does not exist");

        $cost = (int)$this->shop->cost( $data["item"] );

        if( $this->balance->afford( $data["balanceid"], $cost ) == false )
            throw new \RuntimeException("Cannot afford purchase");

        return $cost;
    }
}