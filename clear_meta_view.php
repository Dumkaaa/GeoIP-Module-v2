<?

// Чтобы сделать нормальное отображение меты, необходимо заменить исходный код на данный в "Настройках макета" ТДС магазина
$aTitle = array();
$aDescription = array();
$aKeywords = array();

if (!is_null($Shop_Controller_Show->tag) && Core::moduleIsActive('tag'))
{
   $oTag = Core_Entity::factory('Tag')->getByPath($Shop_Controller_Show->tag);
   if ($oTag)
   {
      $aTitle[] = $oTag->seo_title != '' ? $oTag->seo_title : Core::_('Shop.tag', $oTag->name);
      $aDescription[] = $oTag->seo_description != '' ? $oTag->seo_description : $oTag->name;
      $aKeywords[] = $oTag->seo_keywords != '' ? $oTag->seo_keywords : $oTag->name;
   }
}

if ($Shop_Controller_Show->group && !$Shop_Controller_Show->item)
{
   $oShop_Group = Core_Entity::factory('Shop_Group', $Shop_Controller_Show->group);

   $aTitle[] = $oShop_Group->seo_title != ''
      ? $oShop_Group->seo_title
      : $oShop_Group->name;

   $aDescription[] = $oShop_Group->seo_description != ''
      ? $oShop_Group->seo_description
      : $oShop_Group->name;

   $aKeywords[] = $oShop_Group->seo_keywords != ''
      ? $oShop_Group->seo_keywords
      : $oShop_Group->name;
}

if ($Shop_Controller_Show->item)
{
   $oShop_Item = Core_Entity::factory('Shop_Item', $Shop_Controller_Show->item);

   Core_Page::instance()->title($oShop_Item->seo_title != ''
      ? $oShop_Item->seo_title
      : $oShop_Item->name);

   Core_Page::instance()->description($oShop_Item->seo_description != ''
      ? $oShop_Item->seo_description
      : $oShop_Item->name);

   Core_Page::instance()->keywords($oShop_Item->seo_keywords != ''
      ? $oShop_Item->seo_keywords
      : $oShop_Item->name);
}

if ($Shop_Controller_Show->producer)
{
   $oShop_Producer = Core_Entity::factory('Shop_Producer', $Shop_Controller_Show->producer);
   Core_Page::instance()->title($oShop_Producer->name);
   Core_Page::instance()->description($oShop_Producer->name);
   Core_Page::instance()->keywords($oShop_Producer->name);
}

if ($Shop_Controller_Show->page)
{
   array_unshift($aTitle, $pageName . ' ' . ($Shop_Controller_Show->page + 1));
}

if (count($aTitle))
{
   $aTitle = array_reverse($aTitle);
   $aDescription = array_reverse($aDescription);
   $aKeywords = array_reverse($aKeywords);

   Core_Page::instance()->title(implode($pageSeparator, $aTitle));
   Core_Page::instance()->description(implode($pageSeparator, $aDescription));
   Core_Page::instance()->keywords(implode($pageSeparator, $aKeywords));
}

Core_Page::instance()->object = $Shop_Controller_Show;


// Чтобы сделать нормальное отображение меты, необходимо заменить исходный код на данный в "Настройках макета" ТДС ИС
$aTitle = array();
$aDescription = array();
$aKeywords = array();

if (!is_null($Informationsystem_Controller_Show->tag) && Core::moduleIsActive('tag'))
{
   $oTag = Core_Entity::factory('Tag')->getByPath($Informationsystem_Controller_Show->tag);
   if ($oTag)
   {
      $aTitle[] = $oTag->seo_title != '' ? $oTag->seo_title : Core::_('Informationsystem.tag', $oTag->name);
      $aDescription[] = $oTag->seo_description != '' ? $oTag->seo_description : $oTag->name;
      $aKeywords[] = $oTag->seo_keywords != '' ? $oTag->seo_keywords : $oTag->name;
   }
}

if ($Informationsystem_Controller_Show->group && !$Informationsystem_Controller_Show->item)
{
   $oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $Informationsystem_Controller_Show->group);

   $aTitle[] = $oInformationsystem_Group->seo_title != ''
      ? $oInformationsystem_Group->seo_title
      : $oInformationsystem_Group->name;

   $aDescription[] = $oInformationsystem_Group->seo_description != ''
      ? $oInformationsystem_Group->seo_description
      : $oInformationsystem_Group->name;

   $aKeywords[] = $oInformationsystem_Group->seo_keywords != ''
      ? $oInformationsystem_Group->seo_keywords
      : $oInformationsystem_Group->name;
}

if ($Informationsystem_Controller_Show->item)
{
   $oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item', $Informationsystem_Controller_Show->item);

   Core_Page::instance()->title($oInformationsystem_Item->seo_title != ''
      ? $oInformationsystem_Item->seo_title
      : $oInformationsystem_Item->name);

   Core_Page::instance()->description($oInformationsystem_Item->seo_description != ''
      ? $oInformationsystem_Item->seo_description
      : $oInformationsystem_Item->name);

   Core_Page::instance()->keywords($oInformationsystem_Item->seo_keywords != ''
      ? $oInformationsystem_Item->seo_keywords
      : $oInformationsystem_Item->name);
}
if ($Informationsystem_Controller_Show->page)
{
   array_unshift($aTitle, $pageName . ' ' . ($Informationsystem_Controller_Show->page + 1));
}

if (count($aTitle))
{
   $aTitle = array_reverse($aTitle);
   $aDescription = array_reverse($aDescription);
   $aKeywords = array_reverse($aKeywords);

   Core_Page::instance()->title(implode($pageSeparator, $aTitle));
   Core_Page::instance()->description(implode($pageSeparator, $aDescription));
   Core_Page::instance()->keywords(implode($pageSeparator, $aKeywords));
}

Core_Page::instance()->object = $Informationsystem_Controller_Show;

?>