<?php

/**
 *
 * PHP Pro Bid $Id$ Z4/Sq9zzrx6Pd+C7wBWkpLeysSdsAZiSrM82XdUViNg=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2016 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.7
 */

namespace Ppb\Model\Elements;

class CustomField extends AbstractElements
{

    /**
     *
     * form id
     *
     * @var array
     */
    protected $_formId = array();

    /**
     *
     * element types allowed
     *
     * @var array
     */
    protected $_elements = array(
        'text'     => 'text',
        'select'   => 'select',
        'radio'    => 'radio',
        'checkbox' => 'checkbox',
        'password' => 'password',
        'textarea' => 'textarea',
    );

    /**
     *
     * class constructor
     */
    public function __construct($formId = null)
    {
        parent::__construct();

        $this->_formId = (array)$formId;
    }

    /**
     *
     * @return array
     */
    public function getElements()
    {
        $translate = $this->getTranslate();

        return array(
            array(
                'form_id'      => 'global',
                'id'           => 'element',
                'element'      => 'select',
                'label'        => $this->_('Html Element'),
                'multiOptions' => $this->_elements,
                'description'  => $this->_('Choose the element type you wish to create.'),
                'attributes'   => array(
                    'class' => 'form-control input-medium field-changeable',
                ),
                'bodyCode'     => "
                    <script type=\"text/javascript\">
                        function updateCustomFieldProperties() {
                            var el = $('select[name=\"element\"]').val();

                            if (el == 'select' || el == 'radio' || el == 'checkbox') {
                                $('.multi-options').closest('.form-group').show();
                            }
                            else {
                                $('.multi-options').closest('.form-group').hide();
                            }
                            
                            if (el == 'select') {
                                $('input:checkbox[name=\"multiple\"]').closest('.form-group').show();
                            }
                            else {
                                $('input:checkbox[name=\"multiple\"]').prop('checked', false).closest('.form-group').hide();
                            }

                            if (el == 'checkbox') {
                                $('input:checkbox[name=\"product_attribute\"]').closest('.form-group').show();
                            }
                            else {
                                $('input:checkbox[name=\"product_attribute\"]').prop('checked', false).closest('.form-group').hide();
                            }

                            if ($('input:checkbox[name=\"product_attribute\"]').is(':checked')) {
                                $('input:checkbox[name=\"required\"]').prop('checked', false).closest('.form-group').hide();
                            }
                            else {
                                $('input:checkbox[name=\"required\"]').closest('.form-group').show();
                            }
                        }

                        $(document).ready(function() {             
                            updateCustomFieldProperties();
                        });

                        $(document).on('change', '.field-changeable', function() {
                            updateCustomFieldProperties();
                        });
                    </script>",
            ),
            array(
                'form_id'      => 'global',
                'id'           => 'product_attribute',
                'element'      => 'checkbox',
                'label'        => $this->_('Product Attribute'),
                'description'  => $this->_('Check the above checkbox if this element will be a product attribute.'),
                'multiOptions' => array(
                    1 => null,
                ),
                'attributes'   => array(
                    'class' => 'field-changeable',
                ),
            ),
            array(
                'form_id'     => 'global',
                'id'          => 'label',
                'element'     => 'text',
                'label'       => $this->_('Label'),
                'description' => $this->_('Enter a label for the element.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
                'required'    => true,
            ),
            array(
                'form_id'     => 'global',
                'id'          => 'description',
                'element'     => 'text',
                'label'       => $this->_('Description'),
                'description' => $this->_('Enter a description for the element (optional).'),
                'attributes'  => array(
                    'class' => 'form-control input-xlarge',
                ),
            ),
            array(
                'form_id'     => 'global',
                'id'          => 'subtitle',
                'element'     => 'text',
                'label'       => $this->_('Subtitle'),
                'description' => $this->_('Enter a subtitle for the element. The subtitle can be used for separating '
                    . 'custom fields into different sections (optional).'),
                'attributes'  => array(
                    'class' => 'form-control input-xlarge',
                ),
            ),
            array(
                'form_id'     => 'global',
                'id'          => 'prefix',
                'element'     => 'text',
                'label'       => $this->_('Prefix'),
                'description' => $this->_('Enter a prefix for the element (optional).'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'     => 'global',
                'id'          => 'suffix',
                'element'     => 'text',
                'label'       => $this->_('Suffix'),
                'description' => $this->_('Enter a suffix for the element (optional).'),
                'attributes'  => array(
                    'class' => 'form-control input-mini',
                ),
            ),
            array(
                'form_id'      => 'item',
                'id'           => 'category_ids',
                'element'      => '\\Ppb\\Form\\Element\\ChznSelect',
                'label'        => $this->_('Select Categories'),
                'description'  => $this->_('Choose for which categories this field will apply, or leave selection empty if it should apply to all categories.'),
                'multiOptions' => $this->getCategories()->getMultiOptions(null, null, false, true),
                'attributes'   => array(
                    'style'            => 'width: 350px;',
                    'data-placeholder' => 'Choose Categories...',
                ),
                'multiple'     => true,
            ),
            array(
                'form_id'     => 'global',
                'id'          => 'attributes',
                'element'     => '\\Ppb\\Form\\Element\\MultiKeyValue',
                'label'       => $this->_('Attributes'),
                'description' => $this->_('Add attributes for the element (class, id etc.) (optional).<br>'
                    . 'Recommended attribute: class => form-control'),
                'attributes'  => array(
                    'class' => 'form-control input-medium',
                ),
            ),
            array(
                'form_id'     => 'global',
                'id'          => 'multiOptions',
                'element'     => '\\Ppb\\Form\\Element\\MultiKeyValue',
                'label'       => $this->_('Options'),
                'description' => $this->_('Add options for the element.'),
                'attributes'  => array(
                    'class' => 'form-control input-medium multi-options',
                )
            ),
            array(
                'form_id'      => 'global',
                'id'           => 'required',
                'element'      => 'checkbox',
                'label'        => $this->_('Required'),
                'description'  => $this->_('Check the above checkbox if the element is required.'),
                'multiOptions' => array(
                    1 => null,
                ),
            ),
//            array(
//                'form_id'      => 'global',
//                'id'           => 'multiple',
//                'element'      => 'checkbox',
//                'label'        => $this->_('Multiple'),
//                'description'  => $this->_('Check the above checkbox if the element accepts multiple selections.'),
//                'multiOptions' => array(
//                    1 => null,
//                ),
//            ),
            array(
                'form_id'      => 'item',
                'id'           => 'searchable',
                'element'      => 'checkbox',
                'label'        => $this->_('Searchable'),
                'description'  => $this->_('Check the above checkbox if the element will be searchable.'),
                'multiOptions' => array(
                    1 => null,
                ),
            ),
        );
    }

}

