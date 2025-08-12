<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitSandyaNvr extends Migration
{
    public function up()
    {
        // users
        $this->forge->addField([
            'id'            => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'username'      => ['type'=>'VARCHAR','constraint'=>64],
            'provider'      => ['type'=>'ENUM','constraint'=>['local','ldap'],'default'=>'local'],
            'password_hash' => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'display_name'  => ['type'=>'VARCHAR','constraint'=>128,'null'=>true],
            'email'         => ['type'=>'VARCHAR','constraint'=>191,'null'=>true],
            'ldap_dn'       => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'is_active'     => ['type'=>'TINYINT','constraint'=>1,'default'=>1],
            'last_login_at' => ['type'=>'DATETIME','null'=>true],
            'created_at'    => ['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
            'updated_at'    => ['type'=>'DATETIME','null'=>true,'on update'=>'CURRENT_TIMESTAMP'],
            'deleted_at'    => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('username');
        $this->forge->createTable('users', true);

        // roles
        $this->forge->addField([
            'id'          => ['type'=>'SMALLINT','constraint'=>5,'unsigned'=>true,'auto_increment'=>true],
            'name'        => ['type'=>'ENUM','constraint'=>['superadmin','admin','user']],
            'description' => ['type'=>'VARCHAR','constraint'=>191,'null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('roles', true);

        // user_roles
        $this->forge->addField([
            'user_id'     => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true],
            'role_id'     => ['type'=>'SMALLINT','constraint'=>5,'unsigned'=>true],
            'assigned_by' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>true],
            'assigned_at' => ['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
        ]);
        $this->forge->addKey(['user_id','role_id'], true);
        $this->forge->addForeignKey('user_id','users','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('role_id','roles','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('assigned_by','users','id','SET NULL','CASCADE');
        $this->forge->createTable('user_roles', true);

        // cameras
        $this->forge->addField([
            'id'            => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'name'          => ['type'=>'VARCHAR','constraint'=>128],
            'location'      => ['type'=>'VARCHAR','constraint'=>191,'null'=>true],
            'host'          => ['type'=>'VARCHAR','constraint'=>191],
            'port'          => ['type'=>'INT','unsigned'=>true,'default'=>554],
            'protocol'      => ['type'=>'ENUM','constraint'=>['rtsp','rtmp','srt','hls','http-mjpeg','webrtc'],'default'=>'rtsp'],
            'transport'     => ['type'=>'ENUM','constraint'=>['tcp','udp'],'default'=>'tcp'],
            'stream_path'   => ['type'=>'VARCHAR','constraint'=>255],
            'username'      => ['type'=>'VARCHAR','constraint'=>128,'null'=>true],
            'password_enc'  => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'fps'           => ['type'=>'SMALLINT','unsigned'=>true,'null'=>true],
            'audio_enabled' => ['type'=>'TINYINT','constraint'=>1,'default'=>0],
            'is_recording'  => ['type'=>'TINYINT','constraint'=>1,'default'=>1],
            'notes'         => ['type'=>'TEXT','null'=>true],
            'created_by'    => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>true],
            'created_at'    => ['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
            'updated_at'    => ['type'=>'DATETIME','null'=>true,'on update'=>'CURRENT_TIMESTAMP'],
            'deleted_at'    => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name');
        $this->forge->addKey(['host','port']);
        $this->forge->addForeignKey('created_by','users','id','SET NULL','CASCADE');
        $this->forge->createTable('cameras', true);

        // dashboards
        $this->forge->addField([
            'id'          => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'name'        => ['type'=>'VARCHAR','constraint'=>128],
            'description' => ['type'=>'VARCHAR','constraint'=>191,'null'=>true],
            'created_by'  => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>true],
            'created_at'  => ['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
            'updated_at'  => ['type'=>'DATETIME','null'=>true,'on update'=>'CURRENT_TIMESTAMP'],
            'deleted_at'  => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name');
        $this->forge->addForeignKey('created_by','users','id','SET NULL','CASCADE');
        $this->forge->createTable('dashboards', true);

        // dashboard_cameras
        $this->forge->addField([
            'dashboard_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true],
            'camera_id'    => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true],
            'sort_order'   => ['type'=>'INT','null'=>true],
        ]);
        $this->forge->addKey(['dashboard_id','camera_id'], true);
        $this->forge->addForeignKey('dashboard_id','dashboards','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('camera_id','cameras','id','CASCADE','CASCADE');
        $this->forge->createTable('dashboard_cameras', true);

        // user_dashboards
        $this->forge->addField([
            'user_id'      => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true],
            'dashboard_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true],
            'assigned_by'  => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>true],
            'assigned_at'  => ['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
        ]);
        $this->forge->addKey(['user_id','dashboard_id'], true);
        $this->forge->addForeignKey('user_id','users','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('dashboard_id','dashboards','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('assigned_by','users','id','SET NULL','CASCADE');
        $this->forge->createTable('user_dashboards', true);

        // recordings
        $this->forge->addField([
            'id'           => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'camera_id'    => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true],
            'start_time'   => ['type'=>'DATETIME'],
            'end_time'     => ['type'=>'DATETIME'],
            'duration_sec' => ['type'=>'INT','unsigned'=>true],
            'file_path'    => ['type'=>'VARCHAR','constraint'=>512],
            'file_size'    => ['type'=>'BIGINT','unsigned'=>true,'null'=>true],
            'resolution_w' => ['type'=>'INT','unsigned'=>true,'null'=>true],
            'resolution_h' => ['type'=>'INT','unsigned'=>true,'null'=>true],
            'video_codec'  => ['type'=>'VARCHAR','constraint'=>64,'null'=>true],
            'audio_codec'  => ['type'=>'VARCHAR','constraint'=>64,'null'=>true],
            'has_audio'    => ['type'=>'TINYINT','constraint'=>1,'default'=>0],
            'checksum_sha1'=> ['type'=>'CHAR','constraint'=>40,'null'=>true],
            'created_at'   => ['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['camera_id','start_time']);
        $this->forge->addKey('file_path', false, true, 'FULLTEXT', 191); // index prefix workaround
        $this->forge->addForeignKey('camera_id','cameras','id','CASCADE','CASCADE');
        $this->forge->createTable('recordings', true);

        // snapshots
        $this->forge->addField([
            'id'           => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'camera_id'    => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true],
            'captured_at'  => ['type'=>'DATETIME'],
            'file_path'    => ['type'=>'VARCHAR','constraint'=>512],
            'file_size'    => ['type'=>'BIGINT','unsigned'=>true,'null'=>true],
            'checksum_sha1'=> ['type'=>'CHAR','constraint'=>40,'null'=>true],
            'created_at'   => ['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['camera_id','captured_at']);
        $this->forge->addKey('file_path', false, true, 'FULLTEXT', 191);
        $this->forge->addForeignKey('camera_id','cameras','id','CASCADE','CASCADE');
        $this->forge->createTable('snapshots', true);

        // user_camera_access
        $this->forge->addField([
            'user_id'            => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true],
            'camera_id'          => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true],
            'can_view_live'      => ['type'=>'TINYINT','constraint'=>1,'default'=>1],
            'can_view_recording' => ['type'=>'TINYINT','constraint'=>1,'default'=>1],
        ]);
        $this->forge->addKey(['user_id','camera_id'], true);
        $this->forge->addForeignKey('user_id','users','id','CASCADE','CASCADE');
        $this->forge->addForeignKey('camera_id','cameras','id','CASCADE','CASCADE');
        $this->forge->createTable('user_camera_access', true);

        // audit_logs
        $this->forge->addField([
            'id'          => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'auto_increment'=>true],
            'user_id'     => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>true],
            'action'      => ['type'=>'VARCHAR','constraint'=>64],
            'target_type' => ['type'=>'VARCHAR','constraint'=>64,'null'=>true],
            'target_id'   => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>true,'null'=>true],
            'details'     => ['type'=>'JSON','null'=>true],
            'ip_address'  => ['type'=>'VARCHAR','constraint'=>45,'null'=>true],
            'created_at'  => ['type'=>'DATETIME','null'=>false,'default'=>'CURRENT_TIMESTAMP'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id','created_at']);
        $this->forge->createTable('audit_logs', true);

        // view
        $this->db->query('CREATE OR REPLACE VIEW v_user_effective_cameras AS
            SELECT ud.user_id, dc.camera_id
            FROM user_dashboards ud
            JOIN dashboard_cameras dc ON dc.dashboard_id = ud.dashboard_id');
    }

    public function down()
    {
        $this->db->query('DROP VIEW IF EXISTS v_user_effective_cameras');
        $this->forge->dropTable('audit_logs', true);
        $this->forge->dropTable('user_camera_access', true);
        $this->forge->dropTable('snapshots', true);
        $this->forge->dropTable('recordings', true);
        $this->forge->dropTable('user_dashboards', true);
        $this->forge->dropTable('dashboard_cameras', true);
        $this->forge->dropTable('dashboards', true);
        $this->forge->dropTable('cameras', true);
        $this->forge->dropTable('user_roles', true);
        $this->forge->dropTable('roles', true);
        $this->forge->dropTable('users', true);
    }
}
