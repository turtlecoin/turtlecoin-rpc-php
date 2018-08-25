<p align="center"><a href="https://turtlecoin.lol" target="_blank" style="max-width:50%;"><img src="https://user-images.githubusercontent.com/34389545/35821974-62e0e25c-0a70-11e8-87dd-2cfffeb6ed47.png"></a></p>

---

# TurtleCoin Walletd RPC PHP

TurtleCoin Walletd RPC PHP is a PHP wrapper for the TurtleCoin walletd JSON-RPC interface.

---

1) [About TurtleCoin](#about-turtlecoin)
1) [Install TurtleCoin Walletd RPC PHP](#install-turtlecoin-walletd-rpc-php)
1) [Methods](#methods)
1) [Examples](#examples)
1) [License](#license)

---

## About TurtleCoin

TurtleCoin is a fun, fast, and easy way to send money to friends and businesses.

1) **Fast Transactions**: TurtleCoin is creating blocks every 30 seconds, as opposed to every 10 minutes. Your money travels 20x faster on TurtleCoin than on Bitcoin or BitcoinCash.
1) **Privacy**: TurtleCoin has the same privacy features you'll find in Monero and Aeon. Every transaction is private unless you choose to make it public.
1) **Easy To Use**: We support almost every OS, even on mobile you can make a secured paper-wallet for free, and get started with TurtleCoin in under 5 minutes.
1) **Easy To Mine**: TurtleCoin comes with its own basic CPU miner, but you can also use any Monero mining software you're used to if you'd rather use GPU's or mining pools. We recommend XMR-Stak Unified Miner.
1) **Community**: The TurtleCoin community is very welcoming to all users and developers. You won't get shouted at when things break, and we welcome critiques of our work. Please join us in our [Discord Chat](http://chat.turtlecoin.lol).
1) **Support**: We are growing a community of developers and testers in our GitHub meta-forum. You can help by testing software and submitting bug reports, or even just cheering us on from the sidelines.

## Install TurtleCoin Walletd RPC PHP

This package requires PHP >=7.1.3. Require this package with composer:

```
composer require turtlecoin/turtlecoin-walletd-rpc-php
```

## Methods

| Method        | Params   | Description   |
| ------------- | ------------- | ------------- |
| reset | $viewSecretKey |	reset() method allows you to re-sync your wallet. |
| save |  |	save() method allows you to save your wallet by request. |
| getViewKey |  |	getViewKey() method returns address view key. |
| getSpendKeys | $address |	getSpendKeys() method returns address spend keys. |
| getStatus |  |	getStatus() method returns information about the current RPC Wallet state: block_count, known_block_count, last_block_hash and peer_count. |
| getAddresses |  |	getAddresses() method returns an array of your RPC Wallet's addresses. |
| createAddress | $secretSpendKey, $publicSpendKey |	createAddress() method creates an address. |
| deleteAddress | $address |	deleteAddress() method deletes a specified address. |
| getBalance | $address |	getBalance() method returns a balance for a specified address. If address is not specified, returns a cumulative balance of all RPC Wallet's addresses. |
| getBlockHashes | $firstBlockIndex, $blockCount |	getBlockHashes() method returns an array of block hashes for a specified block range. |
| getTransactionHashes | $blockCount, $firstBlockIndex, $blockHash, $addresses, $paymentId |	getTransactionHashes() method returns an array of block and transaction hashes. |
| getTransactions | $blockCount, $firstBlockIndex, $blockHash, $addresses, $paymentId |	getTransactions() method returns information about the transactions in specified block range or for specified addresses. |
| getUnconfirmedTransactionHashes | $addresses |	getUnconfirmedTransactionHashes() method returns information about the current unconfirmed transaction pool or for a specified addresses. |
| getTransaction | $transactionHash |	getTransaction() method returns information about the specified transaction. |
| sendTransaction | $anonymity, $transfers, $fee, $addresses, $unlockTime, $extra, $paymentId, $changeAddress |	sendTransaction() method creates and sends a transaction. |
| createDelayedTransaction | $anonymity, $transfers, $fee, $addresses, $unlockTime, $extra, $paymentId, $changeAddress |	createDelayedTransaction() method creates but not sends a transaction. |
| getDelayedTransactionHashes |  |	getDelayedTransactionHashes() method returns hashes of delayed transactions. |
| deleteDelayedTransaction | $transactionHash |	deleteDelayedTransaction() method deletes a specified delayed transaction. |
| sendDelayedTransaction | $transactionHash |	sendDelayedTransaction() method sends a specified delayed transaction. |
| sendFusionTransaction | $threshold, $anonymity, $addresses, $destinationAddress |	sendFusionTransaction() method creates and sends a fusion transaction. |
| estimateFusion | $threshold, $addresses |	estimateFusion() method allows to estimate a number of outputs that can be optimized with fusion transactions. |
| getMnemonicSeed | $address |	getMnemonicSeed() method functions nearly the same as getSpendKeys(). It returns the mnemonic seed for the given address. |
| createIntegratedAddress | $address, $paymentId | Combines an address and a paymentId into an 'integrated' address, which contains both in an encoded form. This allows users to not have to supply a payment Id in their transfer, and hence cannot forget it. |
| getinfo |  | getinfo() retrieves the fee and address of the public node you are connected to, that will be applied to each transaction. |

## Examples

```php
use TurtleCoin\Walletd;

$walletd = new Walletd\Client();

$response = $walletd->getBalance($walletAddress);
```

```php
use TurtleCoin\Walletd;

$config = [
    'rpcHost'     => 'http://127.0.0.1',
    'rpcPort'     => 8070,
    'rpcPassword' => 'test',
];

$walletd = new Walletd\Client($config);

$json = $walletd->getBalance($walletAddress)->getBody()->getContents();

echo $json;

> {"id":0,"jsonrpc":"2.0","result":["availableBalance":100,"lockedAmount":50]}
``` 

## License

TurtleCoin Walletd RPC PHP is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
