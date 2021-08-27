<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace PrestaShop\Module\DemoControllerTabs\Controller\Admin;

use PrestaShop\Module\DemoControllerTabs\Controller\Admin\Repositories\AliExpressAttributeRepository;

use PrestaShop\PrestaShop\Adapter\Tools;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class MinimalistController extends FrameworkBundleAdminController
{

    /**
     * AliExpressAttributeRepository object
     *
     * @var array
     */
    protected $aliExpressAttributeRepository;

    /**
     * Create a new controller instance.
     *
     * @param  Webkul\Dropship\Repositories\AliExpressAttributeRepository $aliExpressAttributeRepository
     * @return void
     */
    public function __construct(
        AliExpressAttributeRepository  $aliExpressAttributeRepository
    )
    {
        $this->aliExpressAttributeRepository = $aliExpressAttributeRepository;
    }

    /**
     * Import super attributes
     *
     * @return \Illuminate\Http\Response
     */
    public function importSuperAttributes(Request $request)
    {
        $callback = $request->get('callback');

        $data = $request->get();

        if (isset($data['super_attributes'])) {
            $result = $this->aliExpressAttributeRepository->importSuperAttributes($data['super_attributes']);

            $response = new Response($callback . '(' . json_encode([
                    'success' => true,
                    'data' => $result
                ]) . ')');
        } else {
            $response = new Response($callback . '(' . json_encode([
                    'success' => false,
                    'message' => 'No attributes available.',
                ]) . ')');
        }

        $response->headers->set('Content-Type', 'application/javascript');
        $response->headers->set('Access-Control-Allow-Origin: *');
        $response->headers->set('Access-Control-Allow-Methods: GET, POST');
        $response->headers->set("Access-Control-Allow-Headers: X-Requested-With");

        return $response;
    }
    public function validateUrl(Request $request)
    {
        $callback = $request->get('callback');

        $response = new Response($callback . '(' . json_encode(['success' => true, 'message' => 'Url Validate']) . ')');
        $response->headers->set('Content-Type', 'application/javascript');

        return $response;
    }

    /**
     * Authencate chrome extension user
     *
     * @return \Illuminate\Http\Response
     */
    public function authenticateUser()
    {
        $callback = request()->input('callback');

        $user = request()->input('username');
        $token = request()->input('token');

        $response = ['success' => false];
        /*if ($user != "" && $token != "") {
            $adminUser = core()->getConfigData('dropship.settings.credentials.username');
            $adminToken = core()->getConfigData('dropship.settings.credentials.token');

            if ($adminUser == $user && $adminToken == $token) {
                $response = [
                        'success' => true,
                        'message' => 'Authentication Successfully'
                    ];
            } else {
                $response['message'] = 'Authentication Error';
            }
        }*/

        $response = [
            'success' => true,
            'message' => 'Authentication Successfully'
        ];

        $response = response($callback . '(' . json_encode($response) . ')');
        $response->header('Content-Type', 'application/javascript');

        return $response;
    }
    /**
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('@Modules/democontrollertabs/views/templates/admin/minimalist.html.twig');
    }
}
