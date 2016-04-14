<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Surveypro pluginform class.
 *
 * @package   surveyprofield_checkbox
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/form/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/checkbox/lib.php');

/**
 * The class representing the plugin form
 *
 * @package   surveyprofield_checkbox
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_pluginform extends mod_surveypro_itembaseform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        // Start with common section of the form.
        parent::definition();

        $mform = $this->_form;

        // Get _customdata.
        $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        // Item: options.
        $fieldname = 'options';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyprofield_checkbox'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_checkbox');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_RAW); // PARAM_RAW and not PARAM_TEXT otherwise '<' is not accepted

        // Item: labelother.
        $fieldname = 'labelother';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_checkbox'), array('maxlength' => '64', 'size' => '50'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_checkbox');
        $mform->setType($fieldname, PARAM_TEXT);

        // Item: defaultvalue.
        $fieldname = 'defaultvalue';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyprofield_checkbox'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_checkbox');
        $mform->setType($fieldname, PARAM_TEXT);

        // Item: noanswerdefault.
        $fieldname = 'noanswerdefault';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyprofield_checkbox'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_checkbox');
        $mform->setType($fieldname, PARAM_INT);

        // Item: adjustment.
        $fieldname = 'adjustment';
        $options = array(SURVEYPRO_HORIZONTAL => get_string('horizontal', 'surveyprofield_checkbox'), SURVEYPRO_VERTICAL => get_string('vertical', 'surveyprofield_checkbox'));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_checkbox'), $options);
        $mform->setDefault($fieldname, SURVEYPRO_VERTICAL);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_checkbox');
        $mform->setType($fieldname, PARAM_INT);

        // Item: downloadformat.
        $fieldname = 'downloadformat';
        $options = array(SURVEYPRO_ITEMSRETURNSVALUES => get_string('returnvalues', 'surveyprofield_checkbox'),
                         SURVEYPRO_ITEMRETURNSLABELS => get_string('returnlabels', 'surveyprofield_checkbox'),
                         SURVEYPRO_ITEMRETURNSPOSITION => get_string('returnposition', 'surveyprofield_checkbox'));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_checkbox'), $options);
        $mform->setDefault($fieldname, $item->item_get_friendlyformat());
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_checkbox');
        $mform->setType($fieldname, PARAM_INT);

        // Here I open a new fieldset.
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro'));

        // Item: minimumrequired.
        $fieldname = 'minimumrequired';
        $options = array_combine(range(0, 9), range(0, 9));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_checkbox'), $options);
        $mform->setDefault($fieldname, 0);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_checkbox');
        $mform->setType($fieldname, PARAM_INT);

        $this->add_item_buttons();
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array $errors
     */
    public function validation($data, $files) {
        // Get _customdata.
        // $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        $errors = parent::validation($data, $files);

        // Clean inputs.
        $cleanoptions = surveypro_textarea_to_array($data['options']);
        $cleandefaultvalue = surveypro_textarea_to_array($data['defaultvalue']);
        $cleanlabelother = trim($data['labelother']);

        // Build $value and $label arrays starting from $cleanoptions and $cleanlabelother.
        $values = array();
        $labels = array();

        foreach ($cleanoptions as $option) {
            if (strpos($option, SURVEYPRO_VALUELABELSEPARATOR) === false) {
                $values[] = trim($option);
                $labels[] = trim($option);
            } else {
                $pair = explode(SURVEYPRO_VALUELABELSEPARATOR, $option);
                $values[] = $pair[0];
                $labels[] = $pair[1];
            }
        }
        if (!empty($cleanlabelother)) {
            if (strpos($cleanlabelother, SURVEYPRO_OTHERSEPARATOR) === false) {
                $values[] = '';
                $labels[] = $cleanlabelother;
            } else {
                $pair = explode(SURVEYPRO_OTHERSEPARATOR, $cleanlabelother);
                $values[] = $pair[1];
                $labels[] = $pair[0];
            }
        }

        // First check.
        // Each item of default has to be among options OR has to be == to otherlabel value.
        // This also verify (helped by the third check) that the number of default is not greater than the number of options.
        if (!empty($data['defaultvalue'])) {
            foreach ($cleandefaultvalue as $default) {
                if (!in_array($default, $labels)) {
                    $errors['defaultvalue'] = get_string('ierr_foreigndefaultvalue', 'surveyprofield_checkbox', $default);
                    break;
                }
            }
        }

        // Second check.
        // No answer is not allowed if the item is mandatory.
        if ( isset($data['noanswerdefault']) && (isset($data['required'])) ) {
            $a = get_string('noanswer', 'mod_surveypro');
            $errors['noanswerdefault'] = get_string('ierr_notalloweddefault', 'mod_surveypro', $a);
        }

        // Third check.
        // Each single option item has to be unique.
        // Each single default item has to be unique.
        $arrayunique = array_unique($cleanoptions);
        if (count($cleanoptions) != count($arrayunique)) {
            $errors['options'] = get_string('ierr_optionsduplicated', 'surveyprofield_checkbox');
        }
        $arrayunique = array_unique($cleandefaultvalue);
        if (count($cleandefaultvalue) != count($arrayunique)) {
            $errors['defaultvalue'] = get_string('ierr_defaultsduplicated', 'surveyprofield_checkbox');
        }

        // Fourth check.
        // SURVEYPRO_DBMULTICONTENTSEPARATOR can not be contained into values.
        foreach ($values as $value) {
            if (strpos($value, SURVEYPRO_DBMULTICONTENTSEPARATOR) !== false) {
                if (!empty($cleanlabelother) && ($value == end($values))) { // If $value is the last.
                    $errors['labelother'] = get_string('ierr_optionswithseparator', 'surveyprofield_checkbox', SURVEYPRO_DBMULTICONTENTSEPARATOR);
                } else {
                    $errors['options'] = get_string('ierr_optionswithseparator', 'surveyprofield_checkbox', SURVEYPRO_DBMULTICONTENTSEPARATOR);
                }
                break;
            }
        }

        // Fifth check.
        // Minimumrequired has to be lower than count($cleanoptions).
        if ($data['minimumrequired'] > count($cleanoptions) - 1) {
            $errors['minimumrequired'] = get_string('ierr_minimumrequired', 'surveyprofield_checkbox', count($cleanoptions));
        }

        return $errors;
    }
}
