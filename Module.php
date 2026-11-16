<?php

namespace DclOmekaMisc;

use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Form\Element;
use Omeka\Module\AbstractModule;


class Module extends AbstractModule
{
  const NAMESPACE = __NAMESPACE__;
  
  public function getConfig()

  {
    return include __DIR__ . '/config/module.config.php';
  }

  public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
  {
      // keep any listeners you already have here

      $sharedEventManager->attach(\Omeka\Form\SiteForm::class, 'form.add_elements', [$this, 'addSiteFormElements']);
      $sharedEventManager->attach(\Omeka\Form\SiteForm::class, 'form.add_input_filters', [$this, 'addSiteFormInputFilters']);
      $sharedEventManager->attach(\Omeka\Api\Adapter\SiteAdapter::class, 'api.create.post', [$this, 'saveSiteFields']);
      $sharedEventManager->attach(\Omeka\Api\Adapter\SiteAdapter::class, 'api.update.post', [$this, 'saveSiteFields']);
  }

  /** Default values declared in module.config.php */
  private function getSiteFieldDefaults(): array
  {
      $config = $this->getServiceLocator()->get('Config');
      return $config['dclomekamisc']['site_settings'] ?? [];
  }

  public function addSiteFormElements(Event $event): void
  {
      $form = $event->getTarget();

      $form->add([
          'name' => 'dclomekamisc_tagline',
          'type' => Element\Text::class,
          'options' => [
              'label' => 'Subtitle', // @translate
              'info' => 'Add a short subtitle. Only used for certain themes.' // @translate
          ],
          'attributes' => ['id' => 'dclomekamisc_tagline'],
      ]);

      $site = $this->getCurrentSiteSafe();
      if (!$site) {
          // "Add site" page: show the configured defaults
          foreach ($this->getSiteFieldDefaults() as $name => $default) {
              if ($form->has($name)) {
                  $form->get($name)->setValue($default);
              }
          }
          return;
      }

      $siteSettings = $this->getServiceLocator()->get('Omeka\Settings\Site');
      $siteSettings->setTargetId($site->id());
      foreach ($this->getSiteFieldDefaults() as $name => $default) {
          if ($form->has($name)) {
              $form->get($name)->setValue($siteSettings->get($name, $default));
          }
      }
  }

  public function addSiteFormInputFilters(Event $event): void
  {
      $inputFilter = $event->getParam('inputFilter');
      foreach (array_keys($this->getSiteFieldDefaults()) as $name) {
          $inputFilter->add(['name' => $name, 'required' => false]);
      }
  }

  public function saveSiteFields(Event $event): void
  {
      $data = $event->getParam('request')->getContent();
      $entity = $event->getParam('response')->getContent();
      if (!$entity instanceof \Omeka\Entity\Site) {
          return;
      }

      $siteSettings = $this->getServiceLocator()->get('Omeka\Settings\Site');
      $siteSettings->setTargetId($entity->getId());

      foreach (array_keys($this->getSiteFieldDefaults()) as $name) {
          if (array_key_exists($name, $data)) {
              $siteSettings->set($name, $data[$name]);
          }
      }
  }

  private function getCurrentSiteSafe(): ?\Omeka\Api\Representation\SiteRepresentation
  {
      try {
          return $this->getServiceLocator()->get('ControllerPluginManager')->get('currentSite')();
      } catch (\Throwable $e) {
          return null;
      }
  }
}
