<?php
/**
 * Mkfmn Software
 *
 * @category Magento
 * @package  Mkfmn_CustomCommand
 * @author   Mkfmn
 * @license  https://store.Mkfmn.com/license.html
 */
namespace Mkfmn\CustomCommand\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Registry;

/**
 * Class OrderDeleteCommand
 */
class OrderDeleteCommand extends Command
{
    /**
     * Order argument
     */
    const ORDER_ARGUMENT = 'order_id';

    /**
     * Allow all
     */
    const ALLOW_ALL = 'allow-all';

    /**
     * All order id
     */
    const ALL_ORDER = 'All';

    /**
     * Order
     *
     * @var Order
     */
    private $order;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        Order $order,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        Registry $registry
    )
    {
        $this->order = $order;
        $this->registry = $registry;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('Mkfmn:orderDelete')
            ->setDescription('Order Delete command')
            ->setDefinition([
                new InputArgument(
                    self::ORDER_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'OrderId'
                ),
                new InputOption(
                    self::ALLOW_ALL,
                    '-a',
                    InputOption::VALUE_NONE,
                    'Allow all OrderId'
                ),

            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orderId = $input->getArgument(self::ORDER_ARGUMENT);
        $allowAll = $input->getOption(self::ALLOW_ALL);
        if (is_null($orderId)) {
            if ($allowAll) {
                $this->registry->register('isSecureArea','true');         //create secure area to delete orders
                $orderAllColl = $this->orderCollectionFactory->create();  //Get Order Collection
                foreach ($orderAllColl as $orderCollData) {
                    $orderCollData->delete();                             //Delete Order
                }
                $this->registry->unregister('isSecureArea');              //unset secure area
                $output->writeln('<info>All orders are deleted.</info>'); //Displaying output on terminal
            } else {
                throw new \InvalidArgumentException('Argument ' . self::ORDER_ARGUMENT . ' is missing.');
            }
        }
        if($orderId){
            $orderColl = $this->order->load($orderId);
            if(empty($orderColl) || !$orderColl->getId()){
                $output->writeln('<info>Order with id ' . $orderId . ' does not exist.</info>');
            }else{
                $this->registry->register('isSecureArea','true');         //create secure area to delete orders
                $orderColl->delete();                                     //Delete Order
                $this->registry->unregister('isSecureArea');              //unset secure area
                $output->writeln('<info>Order with id ' . $orderId . ' is deleted.</info>');
            }
        }
    }
}
