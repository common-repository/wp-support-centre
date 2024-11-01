<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $wpdb;
$wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
require_once( WPSC_PLUGIN_DIR . 'assets/lib/googchart_0_1/GoogChart.class.php' );
echo '<div class="wrap wpsc-bootstrap-styles">';
	echo '<h2>Statistics</h2>';
	echo '<div class="panel panel-default">';
        echo '<div class="panel-heading"><h4 class="panel-title">Statistics</h4></div>';
        echo '<div class="panel-body panel-body-wheat">';
			echo '<div class="form-group">';
				echo '<div class="col-xs-12">';
					$chart = new GoogChart();
				    $sql = "SELECT id,status,colour FROM " . $wpdb->prefix . 'wpsc_status WHERE id!=3';
				    $status_array = $wpdb->get_results( $sql );
				    if ( $wpdb->num_rows > 0 ) {
				        $data = array();
				        $colour = array();
				        foreach ( $status_array as $status ) {
				            $sql = "SELECT id FROM " . $wpdb->prefix . "wpsc_tickets WHERE status_id=" . $status->id;
				            $result = $wpdb->get_results( $sql );
				            if ( $wpdb->num_rows > 0 ) {
				                $data[$status->status . ' (' . $wpdb->num_rows . ')'] = $wpdb->num_rows;
				            } else {
				                $data[$status->status . ' (0)'] = 0;
				            }
				            $colour[] = $status->colour;
				        }
				        $chart->setChartAttrs(
				            array(
				                'type' => 'pie',
				                'title' => 'Status',
				                'data' => $data,
				                'size' => array(
				                    800, 300
				                ),
				                'color' => $colour
				            )
				        );
				        echo $chart;
				    }
				echo '</div>';
				echo '<div class="col-xs-12">';
					$chart = new GoogChart();
				    $sql = "SELECT id,category FROM " . $wpdb->prefix . 'wpsc_categories';
				    $categories_array = $wpdb->get_results( $sql );
				    if ( $wpdb->num_rows > 0 ) {
				        $data = array();
				        foreach ( $categories_array as $category ) {
				            $sql = "SELECT id FROM " . $wpdb->prefix . "wpsc_tickets WHERE category_id=" . $category->id . " AND status_id!=3";
				            $result = $wpdb->get_results( $sql );
				            if ( $wpdb->num_rows > 0 ) {
				                $data[$category->category . ' (' . $wpdb->num_rows . ')'] = $wpdb->num_rows;
				            } else {
				                $data[$category->category . ' (0)'] = 0;
				            }
				        }
				        $chart->setChartAttrs(
				            array(
				                'type' => 'pie',
				                'title' => 'Category',
				                'data' => $data,
				                'size' => array(
				                    800, 300
				                )
				            )
				        );
				        echo $chart;
				    }
				echo '</div>';
			echo '</div>';
			echo '<div class="form-group">';
				echo '<div class="col-xs-12">';
					$chart = new GoogChart();
				    $sql = "SELECT id,priority,colour FROM " . $wpdb->prefix . 'wpsc_priority';
				    $priority_array = $wpdb->get_results( $sql );
				    if ( $wpdb->num_rows > 0 ) {
				        $data = array();
				        foreach ( $priority_array as $priority ) {
				            $sql = "SELECT id FROM " . $wpdb->prefix . "wpsc_tickets WHERE priority_id=" . $priority->id . " AND status_id!=3";
				            $result = $wpdb->get_results( $sql );
				            if ( $wpdb->num_rows > 0 ) {
				                $data[$priority->priority . ' (' . $wpdb->num_rows . ')'] = $wpdb->num_rows;
				            } else {
				                $data[$priority->priority . ' (0)'] = 0;
				            }
				            $colour[] = $priority->colour;
				        }
				        $chart->setChartAttrs(
				            array(
				                'type' => 'pie',
				                'title' => 'Priority',
				                'data' => $data,
				                'size' => array(
				                    800, 300
				                ),
				                'color' => $colour
				            )
				        );
				        echo $chart;
				    }
				echo '</div>';
			echo '</div>';
		echo '</div>';
	echo '</div>';
echo '</div>';