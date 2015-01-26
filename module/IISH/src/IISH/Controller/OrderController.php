<?php
namespace IISH\Controller;
use IISH\RecordDriver\SolrAv;
use VuFind\Controller\AbstractBase;

/**
 * Order Controller.
 *
 * @package IISH\Controller
 */
class OrderController extends AbstractBase {
    /**
     * @var array
     */
    private $orderConfig;

    /**
     * Constructor.
     *
     * @param array $orderConfig Order configuration.
     */
    public function __construct(array $orderConfig) {
        parent::__construct();
        $this->orderConfig = $orderConfig;
    }

    /**
     * Home (default) action -- renders and processes the order form.
     *
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function homeAction() {
        $id = $this->params()->fromQuery('id');

        $viewModel = $this->createViewModel();
        $viewModel->setTemplate('order/order.phtml');

        $viewModel->id = $id;
        $viewModel->request = $this->getRequest()->getPost();

        if ($this->formWasSubmitted('submit')) {
            try {
                $this->placeOrder();

                $this->flashMessenger()
                    ->setNamespace('success')
                    ->addMessage('order.sent');

                return $this->lightboxAwareRedirect('record', array('id' => $id));
            }
            catch (\Exception $e) {
                $this->flashMessenger()
                    ->setNamespace('error')
                    ->addMessage($e->getMessage());
            }
        }

        return $viewModel;
    }

    /**
     * Places an order for the specified record.
     *
     * @throws \Exception
     * @throws \VuFind\Exception\RecordMissing
     */
    private function placeOrder() {
        $id = $this->params()->fromQuery('id');
        $email = $this->params()->fromQuery('email');
        $name = $this->params()->fromQuery('name');

        // Obtain the record in question
        $driver = $this->getRecordLoader()->load($id, 'solr');
        if ($driver === null) {
            throw new \Exception('Record does not exist.');
        }

        // See if the user is allowed to place an order for this record
        if (!($driver instanceof SolrAv)) {
            throw new \Exception('Not an audio visual record.');
        }

        $publication = $driver->getPublicationStatus();
        if (($publication === 'minimal') || ($publication === 'closed')) {
            throw new \Exception('You are not allowed to place an order for this record.');
        }

        // Prepare for sending emails
        $viewRenderer = $this->getViewRenderer();
        $mailer = $this->getServiceLocator()->get('VuFind\Mailer');
        $emailModel = array_merge($this->getRequest()->getPost()->toArray(), array(
            'driver'      => $driver,
            'accessToken' => $this->orderConfig['accessToken'],
            'callnumbers' => array_map(function ($ar) {
                return $ar['c'];
            }, $driver->getHoldings())
        ));

        // Send to repo
        $bodyRepo = $viewRenderer->render('order/mail-repo.phtml', $emailModel);
        $mailer->send($this->orderConfig['to'], $this->orderConfig['from'], $this->orderConfig['subject'] . ' ' . $name,
            $bodyRepo);

        // Send to customer
        $bodyCustomer = $viewRenderer->render('order/mail-customer.phtml', $emailModel);
        $mailer->send($email, $this->orderConfig['from'], $this->orderConfig['subject'], $bodyCustomer);
    }
}
