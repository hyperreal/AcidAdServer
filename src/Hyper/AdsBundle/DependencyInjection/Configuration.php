<?php

namespace Hyper\AdsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EmailValidator;
use Symfony\Component\Validator\ExecutionContext;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('hyper_ads');

        $rootNode
            ->children()
                ->scalarNode('order_hash_key')->isRequired()->cannotBeEmpty()->cannotBeOverwritten()->end()
                ->scalarNode('large_price_factor')->isRequired()
                    ->validate()
                        ->ifTrue(
                            function ($factor) {
                                return floatval($factor) != $factor;
                            }
                        )
                        ->thenInvalid('large_price_factor should be a number')
                    ->end()
                ->end()
                ->integerNode('max_banners_in_zone')->isRequired()->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(
                            function ($value) {
                                return $value < 0;
                            }
                        )
                        ->thenInvalid('max_banner_in_zone should be a positive integer')
                    ->end()
                ->end()
                ->scalarNode('order_hash_algorithm')->isRequired()
                    ->validate()
                        ->ifTrue(
                            function ($algorithm) {
                                return !in_array($algorithm, hash_algos());
                            }
                        )
                        ->thenInvalid('Invalid hash algorithm. Should be one of those provided by hash_algos()')
                    ->end()
                ->end()
              ->end()
          ->end();

        $this->addGatewaysConfiguration($rootNode);

        return $treeBuilder;
    }

  private function addGatewaysConfiguration($rootNode) {
    $rootNode
      ->children()
        ->arrayNode('payment_gateways')
          ->children()
            ->arrayNode('bitpay')
              ->children()
                ->scalarNode('name')->isRequired()->end()
                ->scalarNode('api_key')->cannotBeEmpty()->isRequired()->end()
                ->scalarNode('transaction_speed')->isRequired()->defaultValue('medium')
                  ->validate()
                    ->ifNotInArray(array('low', 'medium', 'high'))
                    ->thenInvalid('transaction_speed should be one of: low, medium, high')
                  ->end()
                ->end()
                ->booleanNode('full_notifications')
                  ->isRequired()
                  ->defaultFalse()
                  ->treatNullLike(false)
                ->end()
                ->scalarNode('notifications_email')
                  ->validate()
                    ->ifTrue(
                      function ($e) {
                        return !filter_var($e, FILTER_VALIDATE_EMAIL) && !empty($e);
                      }
                    )
                    ->thenInvalid(
                      'Email invalid in bitpay_notification_email parameter. It could be empty (~)'
                    )
                  ->end() //end validate
                ->end()
              ->end()
            ->end() /*end bitpay*/
            ->arrayNode('electrum')
              ->children()
                ->scalarNode('name')->isRequired()->end()
                ->scalarNode('expiration')->end()
                ->scalarNode('endpoint')->end()
              ->end()
            ->end()
          ->end()
        ->end();
  }
}
