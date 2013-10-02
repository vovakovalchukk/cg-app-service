<?php
namespace CG\Skeleton\Vagrant\NodeData;

use ArrayObject;

class Node extends ArrayObject
{
    const VM_RAM = 'VM-RAM';
    const VM_IP = 'VM-ip';
    const BOX = 'box';
    const CHEF_ATTRIBUTES = 'chef_attributes';
    const CHEF_ROLES = 'chef_roles';
    const CHEF_RECIPES = 'chef_recipes';
    const SYNCED_FOLDERS = 'synced_folders';

    public function getVmRam()
    {
        return $this->offsetGet(static::VM_RAM);
    }

    public function setVmRam($vmRam)
    {
        $this->offsetSet(static::VM_RAM, $vmRam);
        return $this;
    }

    public function getVmIp()
    {
        return $this->offsetGet(static::VM_IP);
    }

    public function setVmIp($vmIp)
    {
        $this->offsetSet(static::VM_IP, $vmIp);
        return $this;
    }

    public function getBox()
    {
        return $this->offsetGet(static::BOX);
    }

    public function setBox($box)
    {
        $this->offsetSet(static::BOX, $box);
        return $this;
    }

    public function getChefAttributes()
    {
        return $this->offsetGet(static::CHEF_ATTRIBUTES) ?: array();
    }

    public function addChefAttribute($chefAttribute)
    {
        $chefAttributes = $this->getChefAttributes();
        $chefAttributes[] = $chefAttribute;
        $this->offsetSet(static::CHEF_ATTRIBUTES, $chefAttributes);
        return $this;
    }

    public function getChefRoles()
    {
        return $this->offsetGet(static::CHEF_ROLES) ?: array();
    }

    public function addChefRole($chefRole)
    {
        $chefRoles = $this->getChefRoles();
        $chefRoles[] = $chefRole;
        $this->offsetSet(static::CHEF_ROLES, $chefRoles);
        return $this;
    }

    public function getChefRecipes()
    {
        return $this->offsetGet(static::CHEF_RECIPES) ?: array();
    }

    public function addChefRecipe($chefRecipe)
    {
        $chefRecipes = $this->getChefRecipes();
        $chefRecipes[] = $chefRecipe;
        $this->offsetSet(static::CHEF_RECIPES, $chefRecipes);
        return $this;
    }

    public function getSyncedFolders()
    {
        return $this->offsetGet(static::SYNCED_FOLDERS) ?: array();
    }

    public function addSyncedFolder($syncedFolder)
    {
        $syncedFolders = $this->getSyncedFolders();
        $syncedFolders[] = $syncedFolder;
        $this->offsetSet(static::SYNCED_FOLDERS, $syncedFolders);
        return $this;
    }
}