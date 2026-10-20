
<script src="<?= BASE_ASSET; ?>/js/jquery.hotkeys.js"></script>
<script type="text/javascript">
    function domo(){
     
       // Binding keys
       $('*').bind('keydown', 'Ctrl+s', function assets() {
          $('#btn_save').trigger('click');
           return false;
       });
    
       $('*').bind('keydown', 'Ctrl+x', function assets() {
          $('#btn_cancel').trigger('click');
           return false;
       });
    
      $('*').bind('keydown', 'Ctrl+d', function assets() {
          $('.btn_save_back').trigger('click');
           return false;
       });
        
    }
    
    jQuery(document).ready(domo);
</script>
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Spin Histories        <small>Edit Spin Histories</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class=""><a  href="<?= site_url('administrator/spin_histories'); ?>">Spin Histories</a></li>
        <li class="active">Edit</li>
    </ol>
</section>
<!-- Main content -->
<section class="content">
    <div class="row" >
        <div class="col-md-12">
            <div class="box box-warning">
                <div class="box-body ">
                    <!-- Widget: user widget style 1 -->
                    <div class="box box-widget widget-user-2">
                        <!-- Add the bg color to the header using any of the bg-* classes -->
                        <div class="widget-user-header ">
                            <div class="widget-user-image">
                                <img class="img-circle" src="<?= BASE_ASSET; ?>/img/add2.png" alt="User Avatar">
                            </div>
                            <!-- /.widget-user-image -->
                            <h3 class="widget-user-username">Spin Histories</h3>
                            <h5 class="widget-user-desc">Edit Spin Histories</h5>
                            <hr>
                        </div>
                        <?= form_open(base_url('administrator/spin_histories/edit_save/'.$this->uri->segment(4)), [
                            'name'    => 'form_spin_histories', 
                            'class'   => 'form-horizontal', 
                            'id'      => 'form_spin_histories', 
                            'method'  => 'POST'
                            ]); ?>
                         
                                                <div class="form-group ">
                            <label for="qr_token_id" class="col-sm-2 control-label">Qr Token Id 
                            <i class="required">*</i>
                            </label>
                            <div class="col-sm-8">
                                <input type="number" class="form-control" name="qr_token_id" id="qr_token_id" placeholder="Qr Token Id" value="<?= set_value('qr_token_id', $spin_histories->qr_token_id); ?>">
                                <small class="info help-block">
                                <b>Input Qr Token Id</b> Max Length : 10.</small>
                            </div>
                        </div>
                                                 
                                                <div class="form-group ">
                            <label for="no_pesanan" class="col-sm-2 control-label">No Pesanan 
                            <i class="required">*</i>
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="no_pesanan" id="no_pesanan" placeholder="No Pesanan" value="<?= set_value('no_pesanan', $spin_histories->no_pesanan); ?>">
                                <small class="info help-block">
                                <b>Input No Pesanan</b> Max Length : 100.</small>
                            </div>
                        </div>
                                                 
                                                <div class="form-group ">
                            <label for="nama_pemenang" class="col-sm-2 control-label">Nama Pemenang 
                            <i class="required">*</i>
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="nama_pemenang" id="nama_pemenang" placeholder="Nama Pemenang" value="<?= set_value('nama_pemenang', $spin_histories->nama_pemenang); ?>">
                                <small class="info help-block">
                                <b>Input Nama Pemenang</b> Max Length : 100.</small>
                            </div>
                        </div>
                                                 
                                                <div class="form-group ">
                            <label for="no_whatsapp" class="col-sm-2 control-label">No Whatsapp 
                            <i class="required">*</i>
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="no_whatsapp" id="no_whatsapp" placeholder="No Whatsapp" value="<?= set_value('no_whatsapp', $spin_histories->no_whatsapp); ?>">
                                <small class="info help-block">
                                <b>Input No Whatsapp</b> Max Length : 30.</small>
                            </div>
                        </div>
                                                 
                                                <div class="form-group ">
                            <label for="alamat_pengiriman" class="col-sm-2 control-label">Alamat Pengiriman 
                            <i class="required">*</i>
                            </label>
                            <div class="col-sm-8">
                                <textarea id="alamat_pengiriman" name="alamat_pengiriman" rows="10" cols="80"> <?= set_value('alamat_pengiriman', $spin_histories->alamat_pengiriman); ?></textarea>
                                <small class="info help-block">
                                </small>
                            </div>
                        </div>
                                                 
                                                <div class="form-group ">
                            <label for="prize_id" class="col-sm-2 control-label">Prize Id 
                            <i class="required">*</i>
                            </label>
                            <div class="col-sm-8">
                                <input type="number" class="form-control" name="prize_id" id="prize_id" placeholder="Prize Id" value="<?= set_value('prize_id', $spin_histories->prize_id); ?>">
                                <small class="info help-block">
                                <b>Input Prize Id</b> Max Length : 10.</small>
                            </div>
                        </div>
                                                 
                                                <div class="form-group ">
                            <label for="jenis_spin" class="col-sm-2 control-label">Jenis Spin 
                            <i class="required">*</i>
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="jenis_spin" id="jenis_spin" placeholder="Jenis Spin" value="<?= set_value('jenis_spin', $spin_histories->jenis_spin); ?>">
                                <small class="info help-block">
                                <b>Input Jenis Spin</b> Max Length : 50.</small>
                            </div>
                        </div>
                                                 
                                                <div class="form-group ">
                            <label for="waktu_spin" class="col-sm-2 control-label">Waktu Spin 
                            <i class="required">*</i>
                            </label>
                            <div class="col-sm-6">
                            <div class="input-group date col-sm-8">
                              <input type="text" class="form-control pull-right datetimepicker" name="waktu_spin"  placeholder="Waktu Spin" id="waktu_spin" value="<?= set_value('waktu_spin', $spin_histories->waktu_spin); ?>">
                            </div>
                            <small class="info help-block">
                            </small>
                            </div>
                        </div>
                                                 
                                                <div class="form-group ">
                            <label for="batas_waktu_klaim" class="col-sm-2 control-label">Batas Waktu Klaim 
                            <i class="required">*</i>
                            </label>
                            <div class="col-sm-6">
                            <div class="input-group date col-sm-8">
                              <input type="text" class="form-control pull-right datetimepicker" name="batas_waktu_klaim"  placeholder="Batas Waktu Klaim" id="batas_waktu_klaim" value="<?= set_value('batas_waktu_klaim', $spin_histories->batas_waktu_klaim); ?>">
                            </div>
                            <small class="info help-block">
                            </small>
                            </div>
                        </div>
                                                 
                                                <div class="form-group ">
                            <label for="status_klaim" class="col-sm-2 control-label">Status Klaim 
                            <i class="required">*</i>
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="status_klaim" id="status_klaim" placeholder="Status Klaim" value="<?= set_value('status_klaim', $spin_histories->status_klaim); ?>">
                                <small class="info help-block">
                                </small>
                            </div>
                        </div>
                                                 
                                                <div class="form-group ">
                            <label for="status_hadiah" class="col-sm-2 control-label">Status Hadiah 
                            <i class="required">*</i>
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="status_hadiah" id="status_hadiah" placeholder="Status Hadiah" value="<?= set_value('status_hadiah', $spin_histories->status_hadiah); ?>">
                                <small class="info help-block">
                                </small>
                            </div>
                        </div>
                                                 
                                                <div class="form-group ">
                            <label for="no_resi" class="col-sm-2 control-label">No Resi 
                            <i class="required">*</i>
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="no_resi" id="no_resi" placeholder="No Resi" value="<?= set_value('no_resi', $spin_histories->no_resi); ?>">
                                <small class="info help-block">
                                <b>Input No Resi</b> Max Length : 100.</small>
                            </div>
                        </div>
                                                 
                                                <div class="form-group ">
                            <label for="jasa_kirim" class="col-sm-2 control-label">Jasa Kirim 
                            <i class="required">*</i>
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="jasa_kirim" id="jasa_kirim" placeholder="Jasa Kirim" value="<?= set_value('jasa_kirim', $spin_histories->jasa_kirim); ?>">
                                <small class="info help-block">
                                <b>Input Jasa Kirim</b> Max Length : 50.</small>
                            </div>
                        </div>
                                                 
                                                <div class="form-group ">
                            <label for="status_wa" class="col-sm-2 control-label">Status Wa 
                            <i class="required">*</i>
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="status_wa" id="status_wa" placeholder="Status Wa" value="<?= set_value('status_wa', $spin_histories->status_wa); ?>">
                                <small class="info help-block">
                                </small>
                            </div>
                        </div>
                                                
                        <div class="message"></div>
                        <div class="row-fluid col-md-7">
                            <button class="btn btn-flat btn-primary btn_save btn_action" id="btn_save" data-stype='stay' title="<?= cclang('save_button'); ?> (Ctrl+s)">
                            <i class="fa fa-save" ></i> <?= cclang('save_button'); ?>
                            </button>
                            <a class="btn btn-flat btn-info btn_save btn_action btn_save_back" id="btn_save" data-stype='back' title="<?= cclang('save_and_go_the_list_button'); ?> (Ctrl+d)">
                            <i class="ion ion-ios-list-outline" ></i> <?= cclang('save_and_go_the_list_button'); ?>
                            </a>
                            <a class="btn btn-flat btn-default btn_action" id="btn_cancel" title="<?= cclang('cancel_button'); ?> (Ctrl+x)">
                            <i class="fa fa-undo" ></i> <?= cclang('cancel_button'); ?>
                            </a>
                            <span class="loading loading-hide">
                            <img src="<?= BASE_ASSET; ?>/img/loading-spin-primary.svg"> 
                            <i><?= cclang('loading_saving_data'); ?></i>
                            </span>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <!--/box body -->
            </div>
            <!--/box -->
        </div>
    </div>
</section>
<!-- /.content -->
<script src="<?= BASE_ASSET; ?>ckeditor/ckeditor.js"></script>
<!-- Page script -->
<script>
    $(document).ready(function(){
      
      CKEDITOR.replace('alamat_pengiriman'); 
      var alamat_pengiriman = CKEDITOR.instances.alamat_pengiriman;
                   
      $('#btn_cancel').click(function(){
        swal({
            title: "Are you sure?",
            text: "the data that you have created will be in the exhaust!",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes!",
            cancelButtonText: "No!",
            closeOnConfirm: true,
            closeOnCancel: true
          },
          function(isConfirm){
            if (isConfirm) {
              window.location.href = BASE_URL + 'administrator/spin_histories';
            }
          });
    
        return false;
      }); /*end btn cancel*/
    
      $('.btn_save').click(function(){
        $('.message').fadeOut();
        $('#alamat_pengiriman').val(alamat_pengiriman.getData());
                    
        var form_spin_histories = $('#form_spin_histories');
        var data_post = form_spin_histories.serializeArray();
        var save_type = $(this).attr('data-stype');
        data_post.push({name: 'save_type', value: save_type});
    
        $('.loading').show();
    
        $.ajax({
          url: form_spin_histories.attr('action'),
          type: 'POST',
          dataType: 'json',
          data: data_post,
        })
        .done(function(res) {
          if(res.success) {
            var id = $('#spin_histories_image_galery').find('li').attr('qq-file-id');
            if (save_type == 'back') {
              window.location.href = res.redirect;
              return;
            }
    
            $('.message').printMessage({message : res.message});
            $('.message').fadeIn();
            $('.data_file_uuid').val('');
    
          } else {
            $('.message').printMessage({message : res.message, type : 'warning'});
          }
    
        })
        .fail(function() {
          $('.message').printMessage({message : 'Error save data', type : 'warning'});
        })
        .always(function() {
          $('.loading').hide();
          $('html, body').animate({ scrollTop: $(document).height() }, 2000);
        });
    
        return false;
      }); /*end btn save*/
      
       
       
           
    
    }); /*end doc ready*/
</script>