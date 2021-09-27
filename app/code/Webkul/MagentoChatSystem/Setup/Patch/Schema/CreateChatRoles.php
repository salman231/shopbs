<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MagentoChatSystem\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class CreateChatRoles implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\Authorization\Model\RulesFactory $rulesFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->roleFactory = $roleFactory;
        $this->rulesFactory = $rulesFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /**
        * Create Chatsystem role
        */
        $role = $this->roleFactory->create();
        $collection = $role->getCollection()
            ->addFieldToFilter('role_name', 'ChatSystem');
        if (!$collection->getSize()) {
            $role->setName('ChatSystem')
                ->setPid(0)
                ->setRoleType(RoleGroup::ROLE_TYPE)
                ->setUserType(UserContextInterface::USER_TYPE_ADMIN);

            $role->save();
            $resource = [
                'Magento_Backend::admin',
                'Webkul_MagentoChatSystem::chatsystem',
                'Webkul_MagentoChatSystem::menu',
                'Webkul_MagentoChatSystem::assigned',
                'Webkul_MagentoChatSystem::assigned_view',
                'Webkul_MagentoChatSystem::agents',
                'Webkul_MagentoChatSystem::AgentRating_view'
            ];
            $this->rulesFactory->create()->setRoleId($role->getId())->setResources($resource)->saveRel();
        }

        $role = $this->roleFactory->create();
        $collection = $role->getCollection()
            ->addFieldToFilter('role_name', 'ChatManager');
        if (!$collection->getSize()) {
                $role->setName('ChatManager')
                ->setPid(0)
                ->setRoleType(RoleGroup::ROLE_TYPE)
                ->setUserType(UserContextInterface::USER_TYPE_ADMIN);
                
            $role->save();
            $resource = [
                'Magento_Backend::admin',
                'Webkul_MagentoChatSystem::chatsystem',
                'Webkul_MagentoChatSystem::menu',
                'Webkul_MagentoChatSystem::assigned',
                'Webkul_MagentoChatSystem::assigned_update',
                'Webkul_MagentoChatSystem::agents',
                'Webkul_MagentoChatSystem::feedback',
                'Webkul_MagentoChatSystem::AgentRating_update',
                'Webkul_MagentoChatSystem::AgentRating_save',
                'Webkul_MagentoChatSystem::AgentRating_view',
                'Magento_Backend::stores',
                'Magento_Backend::stores_settings',
                'Magento_Config::config',
                'Webkul_MagentoChatSystem::config_chatsystem'
            ];
            $this->rulesFactory->create()->setRoleId($role->getId())->setResources($resource)->saveRel();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
