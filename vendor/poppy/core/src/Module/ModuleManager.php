<?php

namespace Poppy\Core\Module;

use Illuminate\Support\Collection;
use Poppy\Core\Module\Repositories\Modules;
use Poppy\Core\Module\Repositories\ModulesHook;
use Poppy\Core\Module\Repositories\ModulesMenu;
use Poppy\Core\Module\Repositories\ModulesPage;
use Poppy\Core\Module\Repositories\ModulesService;
use Poppy\Core\Module\Repositories\ModulesSetting;
use Poppy\Core\Module\Repositories\ModulesUi;

/**
 * Class ModuleManager.
 */
class ModuleManager
{

    /**
     * @var ModulesUi
     */
    private $uiRepository;

    /**
     * @var Modules
     */
    private $repository;

    /**
     * @var Collection
     * @deprecated 3.1
     * @removed    4.0
     */
    private $excepts;

    /**
     * @var ModulesMenu
     */
    private $menuRepository;

    /**
     * @var ModulesPage
     * @deprecated
     */
    private $pageRepository;

    /**
     * @var ModulesSetting
     */
    private $settingRepository;

    /**
     * @var ModulesHook
     */
    private $hooksRepo;

    /**
     * @var ModulesService
     */
    private $serviceRepo;

    /**
     * ModuleManager constructor.
     */
    public function __construct()
    {
        $this->excepts = collect();
    }

    /**
     * @return Collection
     */
    public function enabled(): Collection
    {
        return $this->modules()->enabled();
    }


    /**
     * 返回所有模块信息
     * @return Modules
     */
    public function modules(): Modules
    {
        if (!$this->repository instanceof Modules) {
            $this->repository = new Modules();
            $slugs            = app('poppy')->enabled()->pluck('slug');
            $this->repository->initialize($slugs);
        }
        return $this->repository;
    }

    /**
     * Get a module by name.
     * @param mixed $name name
     * @return Module
     */
    public function get($name): Module
    {
        return $this->modules()->get($name);
    }

    /**
     * Check for module exist.
     * @param mixed $name name
     * @return bool
     */
    public function has($name): bool
    {
        return $this->modules()->has($name);
    }


    /**
     * @return ModulesMenu
     */
    public function menus(): ModulesMenu
    {
        if (!$this->menuRepository instanceof ModulesMenu) {
            $collection = collect();
            $this->modules()->enabled()->each(function (Module $module) use ($collection) {
                $collection->put($module->slug(), $module->get('menus', []));
            });
            $this->menuRepository = new ModulesMenu();
            $this->menuRepository->initialize($collection);
        }

        return $this->menuRepository;
    }

    /**
     * @return ModulesHook
     */
    public function hooks(): ModulesHook
    {
        if (!$this->hooksRepo instanceof ModulesHook) {
            $collect = collect();
            $this->modules()->enabled()->each(function (Module $module) use ($collect) {
                $collect->put($module->slug(), $module->get('hooks', []));
            });
            $this->hooksRepo = new ModulesHook();
            $this->hooksRepo->initialize($collect);
        }

        return $this->hooksRepo;
    }

    /**
     * @return ModulesService
     */
    public function services(): ModulesService
    {
        if (!$this->serviceRepo instanceof ModulesService) {
            $collect = collect();
            $this->modules()->enabled()->each(function (Module $module) use ($collect) {
                $collect->put($module->slug(), $module->get('services', []));
            });
            $this->serviceRepo = new ModulesService();
            $this->serviceRepo->initialize($collect);
        }

        return $this->serviceRepo;
    }

    /**
     * @return Modules
     * @deprecated 3.1
     * @removed    4.0
     */
    public function repository(): Modules
    {
        return $this->modules();
    }


    /**
     * @return array
     * @deprecated 3.1
     * @removed    4.0
     */
    public function getExcepts(): array
    {
        return $this->excepts->toArray();
    }

    /**
     * 为了兼容而存在
     * @deprecated
     */
    public function pages(): ModulesPage
    {
        if (!$this->pageRepository instanceof ModulesPage) {
            $collection = collect();
            $this->modules()->enabled()->each(function (Module $module) use ($collection) {
                $collection->put($module->slug(), $module->get('pages', []));
            });
            $this->pageRepository = new ModulesPage();
            $this->pageRepository->initialize($collection);
        }

        return $this->pageRepository;
    }


    /**
     * @return ModulesUi
     * @deprecated
     */
    public function uis(): ModulesUi
    {
        if (!$this->uiRepository instanceof ModulesUi) {
            $collection = collect();
            $this->modules()->enabled()->each(function (Module $module) use ($collection) {
                $collection->put($module->slug(), $module->get('ui', []));
            });
            $this->uiRepository = new ModulesUi();
            $this->uiRepository->initialize($collection);
        }

        return $this->uiRepository;
    }

    /**
     * @return ModulesSetting
     * @deprecated 3.1
     * @removed    4.0
     */
    public function settings(): ModulesSetting
    {
        if (!$this->settingRepository instanceof ModulesSetting) {
            $collection = collect();
            $this->modules()->enabled()->each(function (Module $module) use ($collection) {
                $collection->put($module->slug(), $module->get('settings', []));
            });
            $this->settingRepository = new ModulesSetting();
            $this->settingRepository->initialize($collection);
        }

        return $this->settingRepository;
    }


    /**
     * @param array $excepts 数据数组
     * @deprecated 3.1
     * @removed    4.0
     */
    public function registerExcept(array $excepts): void
    {
        foreach ($excepts as $except) {
            $this->excepts->push($except);
        }
    }
}
