<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2022 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

class KiemKe_Item extends CommonDBRelation
{
    // From CommonDBRelation

    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';

    public static function getTypeName($nb = 0)
    {
        return _n('Kiem Ke Item', 'Kiem Ke Item', $nb);
    }

    public static function cleanForItem(CommonDBTM $item)
    {
        $temp = new self();
        $temp->deleteByCriteria(
            [
                'itemtype' => $item->getType(),
                'items_id' => $item->getField('id')
            ]
        );
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        return 'Cập nhật Kiểm Kê';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showForItem($item);
        return true;
    }

    /**
     * Show data associated to an item
     *
     * @param $item            CommonDBTM object for which associated domains must be displayed
     * @param $withtemplate (default '')
     *
     * @return bool
     */
    public static function showForItem(CommonDBTM $item, $withtemplate = '')
    {
        global $DB;
        global $CFG_GLPI;

        $ID = $item->getField('id');

        if ($item->isNewID($ID)) {
            return false;
        }

        if (!$item->can($item->fields['id'], READ)) {
            return false;
        }

        $rand         = mt_rand();
        $is_recursive = $item->isRecursive();

        $submitPath = $CFG_GLPI['root_doc'] . "/ajax/ajax_cap_nhat_kiem_ke.php";
        $userId = Session::getLoginUserID();
        $user = new User();
        $user->getFromDB($userId);
        $currentUserEntitiesId = $user->fields['entities_id'];
        $locationsId = $item->getField('locations_id');
        $callback = $_SERVER['HTTP_REFERER'];

        $criteria = [
            'SELECT'    => [
                'glpi_kiemke_items.inventory_date',
                'L2.name as prev_locations_name',
                'L1.name as location_name',
                'glpi_users.name as user_name',
                'glpi_kiemke_items.note',
            ],
            'FROM'      => "glpi_kiemke_items",
            'JOIN' => [
                "glpi_computers"   => [
                    'ON'  => [
                        "glpi_computers"   => 'id',
                        "glpi_kiemke_items"     => 'computer_id'
                    ]
                ],
                "glpi_locations AS L1" => [
                    'ON'  => [
                        "L1"   => 'id',
                        "glpi_kiemke_items"     => 'locations_id',
                    ]
                ],
                "glpi_users" => [
                    'ON'  => [
                        "glpi_users"   => 'id',
                        "glpi_kiemke_items"     => 'users_id'
                    ]
                ],
                "glpi_locations AS L2" => [
                    'ON'  => [
                        "L2"   => 'id',
                        "glpi_kiemke_items"     => 'prev_locations_id',
                    ]
                ],
            ],
            'WHERE'     => [
                "glpi_kiemke_items.computer_id" => $ID
            ],
            'ORDER'  => ['glpi_kiemke_items.inventory_date DESC']
        ];

        $iterator = $DB->request($criteria);
        $number = count($iterator);

        $criteriaLocations = [
            'SELECT'    => [
                '*',
            ],
            'FROM'      => "glpi_locations",
            'WHERE'     => [
                'entities_id' => $currentUserEntitiesId
            ]
        ];
        $iteratorLocations = $DB->request($criteriaLocations);

        $i      = 0;
        $rand    = mt_rand();



        echo "<div class='firstbloc'>";
        // echo "<form method='post' class='form-horizontal' name='kiemke_form$rand'
        //  id='kiemke_form$rand'";

        echo '<table class="table">';
        echo '<tbody>';
        echo '<tr>';
        echo '<td>';
        echo "<input type='button' name='cap_nhat_kiem_ke' id='submit-button' value=\"" . "Cập nhật kiểm kê" . "\" class='btn btn-primary' style='background: #5A9BD5; color: white; margin-right: 15px; min-width: 200px;'>";



        echo "<select class='custom-select custom-select-sm' name='locations_id' style='min-width: 200px;'>";
        foreach ($iteratorLocations as $row) {
            $locationsOptionId = $row['id'];
            $locationName = $row['name'];
            $isSelected = $locationsOptionId == $locationsId;
            echo "<option value='$locationsOptionId' " .  ($isSelected ? "selected" : "") . ">$locationName</option>";
        }
        echo "</select>";
        echo '</td>';

        echo "<td>";
        echo '<textarea class="form-control" id="note" name="note" rows="5" placeholder="Ghi chú kiểm kê"></textarea>';
        echo "</td>";
        echo "</tbody>";
        echo "</table>";

        echo "<input type='hidden' name='computer_id' value='$ID'>";
        // echo "<input type='hidden' name='users_id' value='$userId'>";
        echo "<input type='hidden' name='current_locations_id' value='$locationsId'>";
        echo "<input type='hidden' name='callback' value='$callback'>";

        echo "<script>
        // Attach a click event handler to the submit button
        $( document ).ready(function() {
        $('#submit-button').on('click', function(e) {
            e.preventDefault();
          // Get the value of the text area
          // Send an AJAX request to the API
          $.ajax({
            url: '$submitPath',
            method: 'POST',
            data: {
                current_locations_id: $('input[name=\"current_locations_id\"').val(),
                locations_id: $('select[name=\"locations_id\"').val(),
                computer_id: $('input[name=\"computer_id\"').val(),
                callback: $('input[name=\"callback\"').val(),
                note: $('textarea[name=\"note\"').val(),
            },
            success: function(response) {
              // Handle the response from the API
              const res = JSON.parse(response);

              if (!res.success && res.message) {
                alert(res.message);
              } else {
                alert('Cập nhật thành công!');
                window.location.reload();
              }
            },
          });
        });
    });
      </script>";


        // Html::closeForm();
        echo "</div>";

        $output = '<table class="table">';
        $output .= '<thead>' .
            '<tr >' .
            '<th style="background-color: #4473C5; color: white;">Ngày kiểm kê</th>' .
            '<th style="background-color: #4473C5; color: white;">Vị trí trước kiểm kê</th>' .
            '<th style="background-color: #4473C5; color: white;">Vị trí khi kiểm kê</th>' .
            '<th style="background-color: #4473C5; color: white;">Người kiểm kê</th>' .
            '<th style="background-color: #4473C5; color: white;">Ghi chú</th>' .
            '</tr>' .
            '</thead>';
        $output .= '<tbody>';
        foreach ($iterator as $row) {
            $color = $iterator->key() % 2 == 0 ? '#E9ECF5' : '#CFD5EB';
            // Convert the SQL date string to a DateTime object
            $sql_date = new DateTime($row['inventory_date'], new DateTimeZone(date_default_timezone_get()));
            // Convert the DateTime object to the user's local timezone
            $user_timezone = new DateTimeZone('Asia/Bangkok');
            $sql_date->setTimezone($user_timezone);

            // Format the date string in the desired format
            $formatted_date = $sql_date->format('d/m/Y H:i');

            $output .= '<tr ' . "style=\"background:$color\">";
            $output .= '<td>' . $formatted_date . '</td>';
            $output .= '<td>' . $row['prev_locations_name'] . '</td>';
            $output .= '<td>' . $row['location_name'] . '</td>';
            $output .= '<td>' . $row['user_name'] . '</td>';
            $output .= '<td>' . $row['note'] . '</td>';
            $output .= '</tr>';
        }
        $output .= '</tbody>';
        $output .= '</table>';

        echo $output;

        return true;
    }
}
