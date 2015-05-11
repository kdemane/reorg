<?php
require_once 'application/classes/base.php';
require_once 'application/classes/location.php';

class payment extends base
{
    private $form_id // ref data objects
          , $nature_id
          , $product_id

          , $physician_id // business objects
          , $submitting_manufacturer_or_GPO_id
          , $third_party_id

          , $covered_recipient_type_id // transmitted data
          , $general_transaction_id
          , $NDC_of_associated_overed_drug_or_biological
          , $num_total_payments
          , $program_year
          , $location_id
          , $submitting_manufacturer_GPO_name
          , $total_amount_USD
          , $date_paid
          , $date_published

          , $is_chartity // booleans
          , $is_delayed_in_publication_of_general_payment
          , $is_third_party_recipient;

    function extract(&$payment)
    {
        $this->_reset();

        if ($num_extracted =
            $this->_extract_raw_from_payment($payment,
                                              ['general_transaction_id'                                           => TRUE,
                                              'program_year'                                                      => TRUE,
                                              'payment_publication_date'                                          => TRUE,
                                              'submitting_manufacturer_or_applicable_gpo_name'                    => TRUE,
                                              'covered_recipient_type'                                            => TRUE,
                                              'teaching_hospital_id'                                              => TRUE,
                                              'physician_profile_id'                                              => TRUE,
                                              'recipient_primary_business_street_address_line'                    => 2,
                                              'recipient_city'                                                    => TRUE,
                                              'recipient_state'                                                   => TRUE,
                                              'recipient_zip_code'                                                => TRUE,
                                              'recipient_country'                                                 => TRUE,
                                              'recipient_province'                                                => TRUE,
                                              'recipient_postal_code'                                             => TRUE,
                                              'product_indicator'                                                 => TRUE,
                                              'name_of_associated_covered_drug_or_biological'                     => 5,
                                              'ndc_of_associated_covered_drug_or_biological'                      => 5,
                                              'name_of_associated_covered_device_or_medical_supply'               => 5,
                                              'applicable_manufacturer_or_applicable_gpo_making_payment_id'       => TRUE,
                                              'dispute_status_for_publication'                                    => TRUE,
                                              'date_of_payment'                                                   => TRUE,
                                              'number_of_payments_included_in_total_amount'                       => TRUE,
                                              'form_of_payment_or_transfer_of_value'                              => TRUE,
                                              'nature_of_payment_or_transfer_of_value'                            => TRUE,
                                              'city_of_travel'                                                    => TRUE,
                                              'state_of_travel'                                                   => TRUE,
                                              'country_of_travel'                                                 => TRUE,
                                              'name_of_third_party_entity_receiving_payment_or_transfer_of_value' => TRUE,
                                              'charity_indicator'                                                 => TRUE,
                                              'delay_of_publication_in_general_payment_indicator'                 => TRUE,
                                              'product_indicator'                                                 => TRUE,
                                              'third_party_payment_recipient_indicator'                           => TRUE,
                                              'total_amount_of_payment_usdollars'                                 => TRUE]))
        {
            $this->_import();
            return $num_extracted;
        }

        return FALSE;
    }

    private function _import()
    {
        $this->date_paid = $this->raw['date_of_payment'];
        $this->date_published = $this->raw['payment_publication_date'];
        $this->general_transaction_id = $this->raw['general_transaction_id'];

        $this->physician_id = empty($this->raw['physican_profile_id'])
            ? NULL
            : physician::get($this->db, $this->raw['physician_profile_id']);

        $this->form_id = $this->_fetch_payment_ref_id('form', $this->raw['form_of_payment_or_transfer_of_value']);
        $this->nature_id = $this->_fetch_payment_ref_id('nature', $this->raw['nature_of_payment_or_transfer_of_value']);
        $this->product_id = $this->_fetch_payment_ref_id('product', $this->raw['product_indicator']);

        $this->submitting_manufacturer_or_GPO_id = empty($this->raw['submitting_manufacturer_or_applicable_gpo_name'])
            ? NULL
            : manufacturer_or_GPO::get($this->db, $this->raw['submitting_manufacturer_or_applicable_gpo_name']);

        $this->teaching_hospital_id = empty($this->raw['teaching_hospital_id'])
            ? NULL
            : $this->teaching_hospital_id = hospital::get($this->db, $this->raw['teaching_hospital_id']);

        $this->third_party_id =
            third_party::get($this->db,
                             $this->raw['name_of_third_party_entity_receiving_payment_or_transfer_of_value'],
                             $this->raw['third_party_payment_recipient_indicator']);
        $this->total_amount_USD = $this->raw['total_amount_of_payment_usdollars'];
        $this->num_total_payments = $this->raw['number_of_payments_included_in_total_amount'];
        $this->program_year = $this->raw['program_year'];
        $this->covered_recipient_type_id = recipient::fetch_type_id($this->db, $this->raw['covered_recipient_type']);
        $this->location_id =
            location::fetch($this->db,
                address::create(['address_1' =>
                                     !empty($this->raw['recipient_primary_business_street_address_line'][0]) ?
                                     $this->raw['recipient_primary_business_street_address_line'][0] : NULL,
                                 'address_2' =>
                                     !empty($this->raw['recipient_primary_business_street_address_line'][1]) ?
                                     $this->raw['recipient_primary_business_street_address_line'][1] : NULL,
                                 'city'     => $this->raw['recipient_city'],
                                 'province' => $this->raw['recipient_province'],
                                 'state'    => $this->raw['recipient_state'],
                                 'postal'   => $this->raw['recipient_postal_code'],
                                 'zip'      => $this->raw['recipient_zip_code'],
                                 'country'  => $this->raw['recipient_country']]));
        $this->travel_location_id =
            location::fetch($this->db, address::create(['city'    => $this->raw['travel_city'],
                                                        'state'   => $this->raw['travel_state'],
                                                        'country' => $this->raw['travel_country']]));
        $this->is_charity = ($this->raw['charity_indicator'] == 'No' ? 0 : 1);
        $this->is_delayed_in_publication_of_general_payment =
            ($this->raw['delay_of_publication_in_general_payment_indicator'] == 'No' ? 0 : 1);
        $this->is_disputed_for_publication = ($this->raw['dispute_state_for_publication'] == 'No' ? 0 : 1);
    }

    function _fetch_payment_ref_id($table, $description)
    {
        if ($rows = $this->db->select("SELECT id FROM payment_" . $table . " WHERE description = :description",
                                      [':description' => $description]))
        {
            return $rows[0]['id'];
        }

        $sql = "INSERT INTO payment_" . $table . "
                (
                    description
                )
                VALUES
                (
                    :description
                )";

        if ($this->db->exec($sql, [':description' => $description]))
            return $this->db->last_insert_id();

        return NULL;

    }

    private function _reset()
    {
        $this->raw = [];
        unset($this->date_paid);
        unset($this->date_published);
        unset($this->general_transaction_id);
        unset($this->physician_id);
        unset($this->form_id);
        unset($this->nature_id);
        unset($this->product_id);
        unset($this->submitting_manufacturer_or_GPO_id);
        unset($this->teaching_hospital_id);
        unset($this->third_party_id);
        unset($this->total_amount_USD);
        unset($this->num_total_payments);
        unset($this->program_year);
        unset($this->covered_recipient_type_id);
        unset($this->location_id);
        unset($this->travel_location_id);
        unset($this->is_charity);
        unset($this->is_delayed_in_publication_of_general_payment);
        unset($this->is_disputed_for_publication);
    }

    function save()
    {
        if ($this->_validate())
        {
            $sql = "INSERT INTO payment
                    (
                        date_paid,
                        date_published,
                        transmitted_date_paid,
                        transmitted_date_published,
                        general_transaction_id,
                        physician_id,
                        form_id,
                        nature_id,
                        product_id,
                        submitting_manufacturer_or_GPO_id,
                        teaching_hospital_id,
                        third_party_id,
                        total_amount_USD,
                        num_total_payments,
                        program_year,
                        covered_recipient_type_id,
                        location_id,
                        travel_location_id,
                        is_charity,
                        is_delayed_in_publication_of_general_payment,
                        is_disputed_for_publication
                    )
                    VALUES
                    (
                        :date_paid,
                        :date_published,
                        :transmitted_date_paid,
                        :transmitted_date_published,
                        :general_transaction_id,
                        :physician_id,
                        :form_id,
                        :nature_id,
                        :product_id,
                        :submitting_manufacturer_or_GPO_id,
                        :teaching_hospital_id,
                        :third_party_id,
                        :total_amount_USD,
                        :num_total_payments,
                        :program_year,
                        :covered_recipient_type_id,
                        :location_id,
                        :travel_location_id,
                        :is_charity,
                        :is_delayed_in_publication_of_general_payment,
                        :is_disputed_for_publication
                    )";

            $binds = [':date_paid'                                        => $this->date_paid,
                      ':date_published'                                   => $this->date_published,
                      ':transmitted_date_paid'                            => $this->date_paid,
                      ':transmitted_date_published'                       => $this->date_published,
                      ':general_transaction_id'                           => $this->general_transaction_id,
                      ':physician_id'                                     => $this->physician_id,
                      ':form_id'                                          => $this->form_id,
                      ':nature_id'                                        => $this->nature_id,
                      ':product_id'                                       => $this->product_id,
                      ':submitting_manufacturer_or_GPO_id'                => $this->submitting_manufacturer_or_GPO_id,
                      ':teaching_hospital_id'                             => $this->teaching_hospital_id,
                      ':third_party_id'                                   => $this->third_party_id,
                      ':total_amount_USD'                                 => $this->total_amount_USD,
                      ':num_total_payments'                               => $this->num_total_payments,
                      ':program_year'                                     => $this->program_year,
                      ':covered_recipient_type_id'                        => $this->covered_recipient_type_id,
                      ':location_id'                                      => $this->location_id,
                      ':travel_location_id'                               => $this->travel_location_id,
                      ':is_charity'                                       => $this->is_charity ? 0 : 1,
                      ':is_delayed_in_publication_of_general_payment'     =>
                          $this->is_delayed_in_publication_of_general_payment ? 0 : 1,
                      ':is_disputed_for_publication'                      => $this->is_disputed_for_publication ? 0 : 1];

            if (!$this->db->exec($sql, $binds, TRUE))
            {
                die("Insert blew up");
            }
        }
    }

    private function _validate()
    {
        return TRUE;
    }
}

?>