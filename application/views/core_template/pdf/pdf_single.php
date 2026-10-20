<style type="text/css">
	* {
		font-size: 10px; 
	}
	table tbody tr td, table thead tr th{
		border-collapse: collapse;
		padding: 5px;
	}
	table thead tr th {
		background: #858585;
		color: #fff;
	}
</style>
<page>

<p><h2><?= ucwords($title); ?></h2></p>

				<?php foreach ($fields as $field){ 
					$val = $data->{$field};
									if (in_array($field, ['harga_barang', 'sub_total', 'ongkir', 'total_bayar', 'nilai_hadiah', 'min_omzet_tercapai'])) {
						$val = idr($val);
					}
					if ($field === 'is_active') {
						$val = $val ? 'Aktif' : 'Nonaktif';
					}
					if ($field === 'is_used_spin') {
						$val = $val ? 'Sudah Digunakan' : 'Belum Digunakan';
					}
				?>
					<h3><?= cclang($field); ?></h3>
					<p><?= $val; ?></p>
					<hr>
					
				<?php } ?>
	<code></code>

  <page_footer>
    [[page_cu]]/[[page_nb]]
  </page_footer>
</page>
