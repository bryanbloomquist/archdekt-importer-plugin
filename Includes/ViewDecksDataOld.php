<?php

namespace ArchidektImporter\Includes;

class ViewDecksDataOld
{
    /**
     * Create a new Admin page for viewing all decks data
     */
    public static function viewDecksDataPage()
    {
        add_menu_page(
            'View Decks Data',
            'View Decks Data',
            'manage_options',
            'view-decks-data',
            [self::class, 'viewDecksDataPageContent'],
            'dashicons-editor-table',
            25
        );
    }

    public static function viewDecksDataPageContent()
    {
        $all_decks = get_posts(array(
            'post_type' => 'deck',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
?>
        <div class="wrap deck-table-wrap">
            <h2>View Deck Data</h2>
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th>Deck Name</th>
                        <th>Commander</th>
                        <th>Partner/Background</th>
                        <th>Color Identity</th>
                        <th class="vert-text"><span class="rotate">Salt Sum<sup class="popover" title="The EDH Salt Score is a crowd sourced indicator of how salty cards make players in EDH. The data is aggregated by EDHREC. A card's Salt Score is on a scale of 0 to 4. The Salt sum to the total sum of all salt scores of all cards in your deck.">&#9432;</sup></span></th>
                        <th class="vert-text"><span class="rotate">Deck Value</span></th>
                        <th class="vert-text"><span class="rotate">Total Mana Value</span></th>
                        <th class="vert-text"><span class="rotate">Avg Mana Value</span></th>
                        <th class="vert-text"><span class="rotate">Battles</span></th>
                        <th class="vert-text"><span class="rotate">Planeswalkers</span></th>
                        <th class="vert-text"><span class="rotate">Creatures</span></th>
                        <th class="vert-text"><span class="rotate">Sorceries</span></th>
                        <th class="vert-text"><span class="rotate">Instants</span></th>
                        <th class="vert-text"><span class="rotate">Artifacts</span></th>
                        <th class="vert-text"><span class="rotate">Enchantments</span></th>
                        <th class="vert-text"><span class="rotate">Lands</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_decks as $deck) :
                        echo self::fillTableRow($deck);
                    endforeach; ?>
                </tbody>
            </table>
        </div>
<?php
    }

    public static function fillTableRow($deck)
    {
        $deck_id = $deck->ID;
        $data    = get_post_meta($deck_id);

        $output  = '<tr>';
        $output .= '<th><a href="' . $data['deck_url'][0] . '" target="_blank" rel="noreferrer noopener">' . $data['deck_name'][0] . '</a></th>';
        $output .= '<td>' . $data['commander'][0] . '</td>';
        $output .= '<td>' . $data['partner'][0] . '</td>';
        $output .= '<td class="text-center">' . $data['identity'][0] . '</td>';
        $output .= '<td class="text-center">' . $data['salt_sum'][0] . '</td>';
        $output .= '<td class="text-center">$' . $data['deck_price'][0] . '</td>';
        $output .= '<td class="text-center">' . $data['total_mana'][0] . '</td>';
        $output .= '<td class="text-center">' . $data['average_mana'][0] . '</td>';
        $output .= '<td class="text-center">' . $data['battles'][0] . '</td>';
        $output .= '<td class="text-center">' . $data['planeswalkers'][0] . '</td>';
        $output .= '<td class="text-center">' . $data['creatures'][0] . '</td>';
        $output .= '<td class="text-center">' . $data['sorceries'][0] . '</td>';
        $output .= '<td class="text-center">' . $data['instants'][0] . '</td>';
        $output .= '<td class="text-center">' . $data['artifacts'][0] . '</td>';
        $output .= '<td class="text-center">' . $data['enchantments'][0] . '</td>';
        $output .= '<td class="text-center">' . $data['lands'][0] . '</td>';
        $output .= '</tr>';

        return $output;
    }
}

add_action('admin_menu', [ViewDecksData::class, 'viewDecksDataPage']);
