<?php

class ADMIN_CLASS_SeoSitemapForm extends Form
{
    /**
     * Entities
     *
     * @var array
     */
    protected $entities = array();

    /**
     * Get entities
     *
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct('sitemapForm');

        $this->generateEntities();

        $scheduleOptions = array(
            BOL_SeoService::SITEMAP_UPDATE_DAILY => OW::getLanguage()->text('admin', 'seo_sitemap_update_daily'),
            BOL_SeoService::SITEMAP_UPDATE_WEEKLY => OW::getLanguage()->text('admin', 'seo_sitemap_update_weekly'),
            BOL_SeoService::SITEMAP_UPDATE_MONTHLY => OW::getLanguage()->text('admin', 'seo_sitemap_update_monthly'),
        );

        $scheduleField = new Selectbox('schedule');
        $scheduleField->setHasInvitation(false);
        $scheduleField->setValue(OW::getConfig()->getValue('base', 'seo_sitemap_schedule_update'));
        $scheduleField->setLabel(OW::getLanguage()->text('admin', 'seo_sitemap_schedule_updates'));
        $scheduleField->setOptions($scheduleOptions);
        $scheduleField->addValidator(new InArrayValidator(array_keys($scheduleOptions)));
        $scheduleField->setRequired(true);
        $scheduleField->setDescription(OW::getLanguage()->text('admin', 'seo_sitemap_schedule_updates_desc'));

        $this->addElement($scheduleField);

        // submit
        $submit = new Submit('save');
        $submit->setValue(OW::getLanguage()->text('base', 'edit_button'));
        $this->addElement($submit);
    }

    /**
     * Generate entities
     *
     * @return void
     */
    protected function generateEntities()
    {
        $entities = BOL_SeoService::getInstance()->getSitemapEntities();

        if ( $entities )
        {
            $index = 0;

            foreach ($entities as $entityType => $entityData) {
                $description = !empty($entityData['description'])
                    ? OW::getLanguage()->text($entityData['lang_prefix'], $entityData['description'])
                    : '';

                $entityField = new CheckboxField($entityType);
                $entityField->setLabel(OW::getLanguage()->text($entityData['lang_prefix'], $entityData['label']));
                $entityField->setValue($entityData['enabled']);
                $entityField->setDescription($description);

                $this->addElement($entityField);
                $this->entities[] = $entityField->getName();

                $index++;
            }
        }
    }
}
