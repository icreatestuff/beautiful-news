<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports extends MY_Controller {

	/**
	 * Index Page for this controller.
	 *
	 */
	public function index()
	{	
		$data['sites'] = $this->data['sites'];
		$data['current_site'] = $this->data['current_site'];
		
		$this->load->view('admin/reports/index', $data);
	}
	
	public function future_bookings()
	{	
		$start = date('Y-m-d');
		
		$this->load->model('report_model');
		$data['bookings'] = $this->report_model->get_future_bookings($this->session->userdata('site_id'), $start);
		
		$this->load->view('admin/reports/future_bookings', $data);
	}


	public function outstanding_payment()
	{	
		$this->load->model('report_model');
		$data['bookings'] = $this->report_model->get_outstanding_payment_bookings($this->session->userdata('site_id'));
		
		$this->load->view('admin/reports/outstanding_payment', $data);
	}
	
	
	public function leaving_date()
	{	
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('end_date', 'Leaving Date', 'trim|required');
		
		if ($this->form_validation->run() == FALSE)
		{	
			$this->load->view('admin/reports/leaving_date');
		}
		else
		{
			$data['end_date'] = date('Y-m-d', strtotime($this->input->post('end_date')));
		
			$this->load->model('report_model');
			$data['bookings'] = $this->report_model->get_bookings_from_end_date($this->session->userdata('site_id'), $data['end_date']);
			
			$this->load->view('admin/reports/leaving_date', $data);
		}
	}	
	
	
	public function arrival_date()
	{	
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('start_date', 'Arrival Date', 'trim|required');
		
		if ($this->form_validation->run() == FALSE)
		{	
			$this->load->view('admin/reports/arrival_date');
		}
		else
		{
			$data['start_date'] = date('Y-m-d', strtotime($this->input->post('start_date')));
		
			$this->load->model('report_model');
			$data['bookings'] = FALSE;
			
			$bookings = $this->report_model->get_bookings_from_start_date($this->session->userdata('site_id'), $data['start_date']);
			
			if ($bookings->num_rows() > 0)
			{
				$all_bookings = $bookings->result();
				
				foreach ($bookings->result() as $booking => $val)
				{
				 	// Get extras for this booking
				 	$this->load->model('booking_model');
				 	$all_bookings[$booking]->extras = $this->booking_model->get_booked_extras($val->id);
				}
				
				$data['bookings'] = $all_bookings;
			}
			
			$this->load->view('admin/reports/arrival_date', $data);
		}
	}	
	
	
	public function specific_extra()
	{	
		// Get all extras
		$this->load->model('report_model');
		$data['extras'] = $this->report_model->get_all_extras($this->session->userdata('site_id'));
	
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('extra', 'Extra', 'trim|required');
		
		if ($this->form_validation->run() == FALSE)
		{	
			$this->load->view('admin/reports/specific_extra', $data);
		}
		else
		{
			$data['extra_id'] = $this->input->post('extra');	
			
			// Get extra name
			$data['extra_name'] = $this->report_model->get_extra_name($data['extra_id'])->row()->name;
			
			$bookings = $this->report_model->get_bookings_with_extra($this->session->userdata('site_id'), $data['extra_id']);
			
			if ($bookings->num_rows() > 0)
			{	
				foreach ($bookings->result() as $row)
				{
					// Get accommodation names for each inaccommodations_ids field
					$accommodation_ids = explode("|", $row->accommodation_ids);	
					$accommodation_names = array();
					
					foreach ($accommodation_ids as $accommodation)
					{
						$accommodation_names[] = $this->report_model->get_accommodation_name($accommodation);
					}
					
					$booking_array = array(
						'id'			=> $row->id,
						'booking_ref'	=> $row->booking_ref,
						'accommodation'	=> $accommodation_names,
						'extra_name'	=> $row->extra_name,
						'quantity'		=> $row->quantity,
						'start_date'	=> $row->start_date,
						'end_date'		=> $row->end_date,
						'adults'		=> $row->adults,
						'children'		=> $row->children,
						'babies'		=> $row->babies,
						'total_price'	=> $row->total_price	
					);
					
					$data['bookings'][] = $booking_array;
				}
			}
			
			$this->load->view('admin/reports/specific_extra', $data);
		}
	}	
	
	
	public function hot_tubs_booked()
	{					
		$this->load->model('report_model');
		$data['bookings'] = $this->report_model->get_hot_tub_bookings($this->session->userdata('site_id'));
		
		$this->load->view('admin/reports/hot_tub_bookings', $data);
	}
	
	
	public function export_xls()
	{
		// PHPExcel
		require_once '/var/www/vhosts/thebivouac.co.uk/subdomains/booking/httpsdocs/application/third_party/PHPExcel.php';
		
		$alphabet = array('NULL', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
		$site_id = $this->session->userdata('site_id');
		$title = $this->input->post('report_title');
		$model_function = $this->input->post('model_function');
		$secondary_attribute = $this->input->post('secondary_attribute');
		
		$this->load->model('report_model');
		
		if (isset($secondary_attribute) && !empty($secondary_attribute))
		{
			$query = $this->report_model->$model_function($site_id, $secondary_attribute);
		}
		else
		{
			$query = $this->report_model->$model_function($site_id);
		}		
		
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		
		
		// Set properties
		$objPHPExcel->getProperties()->setCreator("The Bivouac")
									 ->setLastModifiedBy("The Bivouac")
									 ->setTitle($title)
									 ->setSubject($title);
			
		// Loop through rows and add to Excel Object
		if ($query->num_rows())
		{		
			// Loop through all results
			$x = 1;
			foreach ($query->result_array() as $row)
			{		
				$count = count($row);
				
				$objPHPExcel->setActiveSheetIndex(0);
				
				if ($x === 1)
				{
					for ($i = 1; $i <= $count; $i++)
					{
						$objPHPExcel->getActiveSheet()->setCellValue($alphabet[$i] . $x, key($row));
						$objPHPExcel->getActiveSheet()->setCellValue($alphabet[$i] . ($x + 1), current($row));
						next($row);
					}
				}
				else
				{
					for ($i = 1; $i <= $count; $i++)
					{	
						$objPHPExcel->getActiveSheet()->setCellValue($alphabet[$i] . ($x + 1), current($row));
						next($row);
					}
				}
				
				$x++;
		    }
		}
		
		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle($title);

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		
		$file_title = $this->safe_url(date('d-m-Y_H-i') . "_" . $title);
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('/var/www/vhosts/thebivouac.co.uk/subdomains/booking/httpsdocs/reports/' . $file_title . '.xls');
		
		echo json_encode($file_title . '.xls');
	}
	
	
	
	function safe_url($str) {
	    return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($str)), '-');
	}

		
}

/* End of file reports.php */
/* Location: ./application/controllers/admin/reports.php */