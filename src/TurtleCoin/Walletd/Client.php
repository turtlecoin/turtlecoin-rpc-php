<?php

namespace TurtleCoin\Walletd;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use stdClass;

class Client
{
    /** @var ClientInterface */
    protected $client = null;

    /** @var int */
    protected $rpcId = 0;

    /** @var string */
    protected $rpcHost = 'http://127.0.0.1';

    /** @var int */
    protected $rpcPort = 8070;

    /** @var string */
    protected $rpcPassword = 'test';

    /**
     * Wrapper for TurtleCoin walletd JSON-RPC interface.
     *
     * @param array $config Configuration options
     */
    public function __construct(array $config = [])
    {
        $this->configure($config);

        $this->client = new GuzzleClient(empty($config['handler']) ? [] : ['handler' => $config['handler']]);
    }

    /**
     * Applies configuration options.
     *
     * @param array $config Configuration options:
     *                      rpcHost: The hostname of the walletd daemon (must include http://)
     *                      rpcPort: The port number the walletd daemon is listening on
     *                      rpcPassword: The password for walletd's JSON-RPC interface
     */
    public function configure(array $config = []):void
    {
        $config = array_intersect_key($config, array_flip(['rpcHost', 'rpcPort', 'rpcPassword']));

        foreach ($config as $key => $value)
        {
            $this->{$key} = $value;
        }
    }

    /**
     * Returns array of configuration options.
     *
     * @return array
     */
    public function config():array
    {
        return [
            'rpcHost'     => $this->rpcHost,
            'rpcPort'     => $this->rpcPort,
            'rpcPassword' => $this->rpcPassword,
        ];
    }

    /**
     * @param string $method
     * @param array  $params
     * @return ResponseInterface
     */
    public function request(string $method, array $params = []):ResponseInterface
    {
        $options = [
            'jsonrpc' => '2.0',
            'id'      => $this->rpcId,
            'method'  => $method,
            'params'  => $this->prepareParams($params)
        ];

        if (!empty($this->rpcPassword)) $options['password'] = $this->rpcPassword;

        $response = $this->client->post($this->uri(), [RequestOptions::JSON => $options]);

        $this->rpcId++;

        return $response;
    }

    /**
     * @param array $params
     * @return array|stdClass
     */
    protected function prepareParams(array $params)
    {
        return empty($params) ? new stdClass() : $params;
    }

    /**
     * Returns the walletd endpoint URI.
     *
     * @return string
     */
    public function uri():string
    {
        return "$this->rpcHost:$this->rpcPort/json_rpc";
    }

    /**
     * @return ClientInterface
     */
    public function client():ClientInterface
    {
        return $this->client;
    }

    /**
     * Re-syncs the wallet.
     *
     * If the $viewSecretKey parameter is not specified, the reset() method resets the wallet and
     * re-syncs it. If the $viewSecretKey argument is specified, reset() method substitutes
     * the existing wallet with a new one with a specified $viewSecretKey and creates an
     * address for it.
     *
     * @param string|null $viewSecretKey Private view key. Optional.
     * @return ResponseInterface
     */
    public function reset(string $viewSecretKey = null):ResponseInterface
    {
        $params = [];

        if (!is_null($viewSecretKey)) $params['viewSecretKey'] = $viewSecretKey;

        return $this->request('reset', $params);
    }

    /**
     * Saves the wallet.
     *
     * @return ResponseInterface
     */
    public function save():ResponseInterface
    {
        return $this->request('save', []);
    }

    /**
     * Returns the view key.
     *
     * @return ResponseInterface
     */
    public function getViewKey():ResponseInterface
    {
        return $this->request('getViewKey', []);
    }

    /**
     * Returns the spend keys.
     *
     * @param string $address Valid and existing in this container address. Required.
     * @return ResponseInterface
     */
    public function getSpendKeys(string $address):ResponseInterface
    {
        $params = [
            'address' => $address
        ];

        return $this->request('getSpendKeys', $params);
    }

    /**
     * Returns information about the current RPC Wallet state:
     * block_count, known_block_count, last_block_hash and peer_count.
     *
     * @return ResponseInterface
     */
    public function getStatus():ResponseInterface
    {
        return $this->request('getStatus', []);
    }

    /**
     * Returns an array of your RPC Wallet's addresses.
     *
     * @return ResponseInterface
     */
    public function getAddresses():ResponseInterface
    {
        return $this->request('getAddresses', []);
    }

    /**
     * Creates an additional address in your wallet.
     *
     * @param string|null $secretSpendKey Private spend key. If secretSpendKey was specified,
     *                                    RPC Wallet creates spend address. Optional.
     * @param string|null $publicSpendKey Public spend key. If publicSpendKey was specified,
     *                                    RPC Wallet creates view address. Optional.
     * @return ResponseInterface
     */
    public function createAddress(string $secretSpendKey = null, string $publicSpendKey = null):ResponseInterface
    {
        $params = [];

        if (!is_null($secretSpendKey)) $params['secretSpendKey'] = $secretSpendKey;
        if (!is_null($publicSpendKey)) $params['publicSpendKey'] = $publicSpendKey;

        return $this->request('createAddress', $params);
    }

    /**
     * Deletes a specified address.
     *
     * @param string $address An address to be deleted. Required.
     * @return ResponseInterface
     */
    public function deleteAddress(string $address):ResponseInterface
    {
        $params = [
            'address' => $address
        ];

        return $this->request('deleteAddress', $params);
    }

    /**
     * Method returns a balance for a specified address.
     *
     * @param string|null $address Valid and existing address in this wallet. Optional.
     * @return ResponseInterface
     */
    public function getBalance(string $address = null):ResponseInterface
    {
        $params = [];

        if (!is_null($address)) $params['address'] = $address;

        return $this->request('getBalance', $params);
    }

    /**
     * Returns an array of block hashes for a specified block range.
     *
     * @param int $firstBlockIndex Starting height. Required.
     * @param int $blockCount      Number of blocks to process. Required.
     * @return ResponseInterface
     */
    public function getBlockHashes(int $firstBlockIndex, int $blockCount):ResponseInterface
    {
        $params = [
            'firstBlockIndex' => $firstBlockIndex,
            'blockCount'      => $blockCount,
        ];

        return $this->request('getBlockHashes', $params);
    }

    /**
     * Returns an array of block and transaction hashes. Transaction consists of transfers.
     * Transfer is an amount-address pair. There could be several transfers in a single transaction.
     *
     * @param int         $blockCount      Number of blocks to return transaction hashes from. Required.
     * @param int|null    $firstBlockIndex Starting height. Only allowed without $blockHash.
     * @param string|null $blockHash       Hash of the starting block. Only allowed without $firstBlockIndex.
     * @param array|null  $addresses       Array of strings, where each string is an address. Optional.
     * @param string|null $paymentId       Valid payment_id. Optional.
     * @return ResponseInterface
     */
    public function getTransactionHashes(
        int $blockCount,
        int $firstBlockIndex = null,
        string $blockHash = null,
        array $addresses = null,
        string $paymentId = null
    ):ResponseInterface {
        $params = [
            'blockCount' => $blockCount,
        ];

        if (!is_null($firstBlockIndex)) $params['firstBlockIndex'] = $firstBlockIndex;
        if (!is_null($blockHash)) $params['blockHash'] = $blockHash;
        if (!is_null($addresses)) $params['addresses'] = $addresses;
        if (!is_null($paymentId)) $params['paymentId'] = $paymentId;

        return $this->request('getTransactionHashes', $params);
    }

    /**
     * Returns an array of block and transaction hashes. Transaction consists of transfers.
     * Transfer is an amount-address pair. There could be several transfers in a single transaction.
     *
     * @param int         $blockCount      Number of blocks to return transaction hashes from. Required.
     * @param int|null    $firstBlockIndex Starting height. Only allowed without $blockHash.
     * @param string|null $blockHash       Hash of the starting block. Only allowed without $firstBlockIndex.
     * @param array|null  $addresses       Array of strings, where each string is an address. Optional.
     * @param string|null $paymentId       Valid payment_id. Optional.
     * @return ResponseInterface
     */
    public function getTransactions(
        int $blockCount,
        int $firstBlockIndex = null,
        string $blockHash = null,
        array $addresses = null,
        string $paymentId = null
    ):ResponseInterface {
        $params = [
            'blockCount' => $blockCount,
        ];

        if (!is_null($firstBlockIndex)) $params['firstBlockIndex'] = $firstBlockIndex;
        if (!is_null($blockHash)) $params['blockHash'] = $blockHash;
        if (!is_null($addresses)) $params['addresses'] = $addresses;
        if (!is_null($paymentId)) $params['paymentId'] = $paymentId;

        return $this->request('getTransactions', $params);
    }

    /**
     * Returns information about the current unconfirmed transaction pool or for a specified addresses.
     * Transaction consists of transfers. Transfer is an amount-address pair. There could be several
     * transfers in a single transaction.
     *
     * @param array|null $addresses Array of strings, where each string is a valid address. Optional.
     * @return ResponseInterface
     */
    public function getUnconfirmedTransactionHashes(array $addresses = null):ResponseInterface
    {
        $params = [];

        if (!is_null($addresses)) $params['addresses'] = $addresses;

        return $this->request('getUnconfirmedTransactionHashes', $params);
    }

    /**
     * Returns information about a particular transaction. Transaction consists of transfers.
     * Transfer is an amount-address pair. There could be several transfers in a single transaction.
     *
     * @param string $transactionHash Hash of the requested transaction. Required.
     * @return ResponseInterface
     */
    public function getTransaction(string $transactionHash):ResponseInterface
    {
        $params = [
            'transactionHash' => $transactionHash
        ];

        return $this->request('getTransaction', $params);
    }

    /**
     * Allows you to send transaction to one or several addresses. Also, it allows you to use a payment_id for a
     * transaction to a single address.
     *
     * @param int         $anonymity      Privacy level (a discrete number from 1 to infinity).
     *                                    6+ recommended. Required.
     * @param array       $transfers      Array that contains: address as string, amount as integer. Required.
     * @param int         $fee            Transaction fee. Required.
     * @param array|null  $addresses      Array of strings, where each string is an address to take the funds
     *                                    from. Optional.
     * @param int|null    $unlockTime     Height of the block until which transaction is going to be locked for
     *                                    spending. Optional.
     * @param string|null $extra          String of variable length. Can contain A-Z, 0-9 characters. Optional.
     * @param string|null $paymentId      Optional.
     * @param string|null $changeAddress  Valid and existing in this container address. If container contains only
     *                                    1 address, $changeAddress field can be left empty and the change is going to
     *                                    be sent to this address. If $addresses field contains only 1 address,
     *                                    $changeAddress can be left empty and the change is going to be sent to this
     *                                    address. In the rest of the cases, $changeAddress field is mandatory and
     *                                    must contain an address.
     * @return ResponseInterface
     */
    public function sendTransaction(
        int $anonymity,
        array $transfers,
        int $fee,
        array $addresses = null,
        int $unlockTime = null,
        string $extra = null,
        string $paymentId = null,
        string $changeAddress = null
    ):ResponseInterface {
        $params = [
            'anonymity' => $anonymity,
            'transfers' => $transfers,
            'fee'       => $fee,
        ];

        if (!is_null($addresses)) $params['addresses'] = $addresses;
        if (!is_null($unlockTime)) $params['unlockTime'] = $unlockTime;
        if (!is_null($extra)) $params['extra'] = $extra;
        if (!is_null($paymentId)) $params['paymentId'] = $paymentId;
        if (!is_null($changeAddress)) $params['changeAddress'] = $changeAddress;

        return $this->request('sendTransaction', $params);
    }

    /**
     * Creates a delayed transaction. Such transactions are not sent into the network automatically and should be
     * pushed using sendDelayedTransaction method.
     *
     * @param int         $anonymity      Privacy level (a discrete number from 1 to infinity).
     *                                    6+ recommended. Required.
     * @param array       $transfers      Array that contains: address as string, amount as integer. Required.
     * @param int         $fee            Transaction fee. Required.
     * @param array|null  $addresses      Array of strings, where each string is an address to take the funds
     *                                    from. Optional.
     * @param int|null    $unlockTime     Height of the block until which transaction is going to be locked for
     *                                    spending. Optional.
     * @param string|null $extra          String of variable length. Can contain A-Z, 0-9 characters. Optional.
     * @param string|null $paymentId      Optional.
     * @param string|null $changeAddress  Valid and existing in this container address. If container contains only
     *                                    1 address, $changeAddress field can be left empty and the change is going to
     *                                    be sent to this address. If $addresses field contains only 1 address,
     *                                    $changeAddress can be left empty and the change is going to be sent to this
     *                                    address. In the rest of the cases, $changeAddress field is mandatory and
     *                                    must contain an address.
     * @return ResponseInterface
     */
    public function createDelayedTransaction(
        int $anonymity,
        array $transfers,
        int $fee,
        array $addresses = null,
        int $unlockTime = null,
        string $extra = null,
        string $paymentId = null,
        string $changeAddress = null
    ):ResponseInterface {
        $params = [
            'anonymity' => $anonymity,
            'transfers' => $transfers,
            'fee'       => $fee,
        ];

        if (!is_null($addresses)) $params['addresses'] = $addresses;
        if (!is_null($unlockTime)) $params['unlockTime'] = $unlockTime;
        if (!is_null($extra)) $params['extra'] = $extra;
        if (!is_null($paymentId)) $params['paymentId'] = $paymentId;
        if (!is_null($changeAddress)) $params['changeAddress'] = $changeAddress;

        return $this->request('createDelayedTransaction', $params);
    }

    /**
     * Returns hashes of delayed transactions.
     *
     * @return ResponseInterface
     */
    public function getDelayedTransactionHashes():ResponseInterface
    {
        return $this->request('getDelayedTransactionHashes', []);
    }

    /**
     * Deletes a specified delayed transaction.
     *
     * @param string $transactionHash Valid, existing delayed transaction. Required.
     * @return ResponseInterface
     */
    public function deleteDelayedTransaction(string $transactionHash):ResponseInterface
    {
        $params = [
            'transactionHash' => $transactionHash
        ];

        return $this->request('deleteDelayedTransaction', $params);
    }

    /**
     * Sends a specified delayed transaction.
     *
     * @param string $transactionHash Valid, existing delayed transaction. Required.
     * @return ResponseInterface
     */
    public function sendDelayedTransaction(string $transactionHash):ResponseInterface
    {
        $params = [
            'transactionHash' => $transactionHash
        ];

        return $this->request('sendDelayedTransaction', $params);
    }

    /**
     * Allows you to send a fusion transaction, by taking funds from selected
     * addresses and transferring them to the destination address.
     *
     * @param int         $threshold          Value that determines which outputs will be optimized. Only the outputs,
     *                                        lesser than the threshold value, will be included into a fusion
     *                                        transaction. Required.
     * @param int         $anonymity          Privacy level (a discrete number from 1 to infinity). Level 6 and higher
     *                                        is recommended. Required.
     * @param array|null  $addresses          Array of strings, where each string is an address to take the funds from.
     *                                        Optional.
     * @param string|null $destinationAddress An address that the optimized funds will be sent to. Valid and existing
     *                                        in this container address. If container contains only 1 address,
     *                                        $destinationAddress field can be left empty and the funds are going to be
     *                                        sent to this address. If $addresses field contains only 1 address,
     *                                        $destinationAddress can be left empty and the funds are going to be sent
     *                                        to this address. In the rest of the cases, $destinationAddress field is
     *                                        mandatory and must contain an address.
     * @return ResponseInterface
     */
    public function sendFusionTransaction(
        int $threshold,
        int $anonymity,
        array $addresses = null,
        string $destinationAddress = null
    ):ResponseInterface {
        $params = [
            'threshold' => $threshold,
            'anonymity' => $anonymity,
        ];

        if (!is_null($addresses)) $params['addresses'] = $addresses;
        if (!is_null($destinationAddress)) $params['destinationAddress'] = $destinationAddress;

        return $this->request('sendFusionTransaction', $params);
    }

    /**
     * Counts the number of unspent outputs of the specified addresses and returns how many of those outputs can be
     * optimized. This method is used to understand if a fusion transaction can be created. If fusionReadyCount returns
     * a value = 0, then a fusion transaction cannot be created.
     *
     * @param int        $threshold Value that determines which outputs will be optimized. Only the outputs, lesser
     *                              than the threshold value, will be included into a fusion transaction. Required.
     * @param array|null $addresses Array of strings, where each string is an address to take the funds from. Optional.
     * @return ResponseInterface
     */
    public function estimateFusion(int $threshold, array $addresses = null):ResponseInterface
    {
        $params = [
            'threshold' => $threshold,
        ];

        if (!is_null($addresses)) $params['addresses'] = $addresses;

        return $this->request('sendFusionTransaction', $params);
    }

    /**
     * Functions nearly the same as getSpendKeys(). It returns the mnemonic seed for the given address. However,
     * because walletd supports multiple addresses in one container, not all wallets can have a mnemonic seed, and so
     * the RPC request will return an error notifying the user the their private keys aren't deterministic.
     *
     * @param string $address Required.
     * @return ResponseInterface
     */
    public function getMnemonicSeed(string $address):ResponseInterface
    {
        $params = [
            'address' => $address,
        ];

        return $this->request('getMnemonicSeed', $params);
    }

    /**
     * Combines an address and a paymentId into an 'integrated' address, which contains both in an encoded form.
     * This allows users to not have to supply a payment Id in their transfer, and hence cannot forget it.
     *
     * @param string $address Required.
     * @param string $paymentId Required.
     * @return ResponseInterface
     */
    public function createIntegratedAddress(string $address, string $paymentId):ResponseInterface
    {
        $params = [
            'address' => $address,
            'paymentId' => $paymentId,
        ];

        return $this->request('createIntegratedAddress', $params);
    }

    /**
     * Retrieves the address, and fee for the node that the turtle-service instance is connected to.
     * This may be null, for example if you are using your own local node. This fee will be sent to
     * the owners address on each sendTransaction() and sendDelayedTransaction() request automatically.
     * Note that it does not apply to sendFusionTransaction().
     *
     * @return ResponseInterface
     */
    public function getinfo():ResponseInterface
    {
        return $this->request('getinfo', []);
    }
}
