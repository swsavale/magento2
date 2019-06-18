<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Model\Review;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Delete action.
 */
class Delete extends ProductController implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var Review
     */
    private $model;

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $reviewId = $this->getRequest()->getParam('id', false);
        try {
            $this->getModel()->aggregate()->delete();

            $this->messageManager->addSuccess(__('The review has been deleted.'));
            if ($this->getRequest()->getParam('ret') == 'pending') {
                $resultRedirect->setPath('review/*/pending');
            } else {
                $resultRedirect->setPath('review/*/');
            }
            return $resultRedirect;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong  deleting this review.'));
        }

        return $resultRedirect->setPath('review/*/edit/', ['id' => $reviewId]);
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        if ($this->_authorization->isAllowed('Magento_Review::reviews_all')) {
            return true;
        }

        if (!$this->_authorization->isAllowed('Magento_Review::pending')) {
            return  false;
        }

        if ($this->getModel()->getStatusId() != Review::STATUS_PENDING) {
            $this->messageManager->addErrorMessage(
                __('Sorry, You have not permission to do this. The Review is not in Pending status.')
            );

            return false;
        }

        return true;
    }

    /**
     * Returns requested model.
     *
     * @return Review
     */
    private function getModel(): Review
    {
        if (!$this->model) {
            $this->model = $this->reviewFactory->create()
                ->load($this->getRequest()->getParam('id', false));
        }

        return $this->model;
    }
}
