<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $uuid
 * @property string $approvable_type
 * @property int $approvable_id
 * @property int $user_id
 * @property string $status_from
 * @property string $status_to
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $approvable
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereApprovableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereApprovableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereStatusFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereStatusTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLog whereUuid($value)
 */
	class ApprovalLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $uuid
 * @property string $attachable_type
 * @property int $attachable_id
 * @property string $nama_file
 * @property string $path_url
 * @property string $ukuran_file
 * @property string $konteks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereAttachableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereAttachableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereKonteks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereNamaFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment wherePathUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereUkuranFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attachment whereUuid($value)
 */
	class Attachment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property int $category_id
 * @property string $code_item
 * @property string $name
 * @property string $type
 * @property string $status
 * @property int|null $units
 * @property string|null $image_item
 * @property string|null $location
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApprovalLog> $approvalLogs
 * @property-read int|null $approval_logs_count
 * @property-read \App\Models\ItemCategory $category
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereCodeItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereImageItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereUnits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Item whereUuid($value)
 */
	class Item extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ItemCategory whereUuid($value)
 */
	class ItemCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $uuid
 * @property int $maintenance_id
 * @property string $nama_item
 * @property string|null $image_item
 * @property int|null $qty
 * @property string|null $satuan
 * @property numeric|null $estimasi_biaya_satuan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MaintenanceRequest $maintenanceRequest
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereEstimasiBiayaSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereImageItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereMaintenanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereNamaItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceItem whereUuid($value)
 */
	class MaintenanceItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $uuid
 * @property int $spk_id
 * @property string|null $tanggal_selesai_aktual
 * @property string $status
 * @property string|null $ringkasan_tindakan
 * @property numeric|null $realisasi_biaya
 * @property string $jadwal_preventif_berikutnya
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attachment> $attachments
 * @property-read int|null $attachments_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereJadwalPreventifBerikutnya($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereRealisasiBiaya($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereRingkasanTindakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereSpkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereTanggalSelesaiAktual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRekap whereUuid($value)
 */
	class MaintenanceRekap extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $uuid
 * @property string $nomor_pengajuan
 * @property int $item_id
 * @property int $requester_id
 * @property string $title
 * @property string $priority
 * @property string $type
 * @property string|null $description
 * @property int|null $estimated_day
 * @property string|null $target_completion_expectations
 * @property numeric|null $total_estimated_cost
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApprovalLog> $approvalLogs
 * @property-read int|null $approval_logs_count
 * @property-read \App\Models\Item $item
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MaintenanceItem> $maintenanceItems
 * @property-read int|null $maintenance_items_count
 * @property-read \App\Models\User $requester
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereEstimatedDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereNomorPengajuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereRequesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereTargetCompletionExpectations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereTotalEstimatedCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaintenanceRequest whereUuid($value)
 */
	class MaintenanceRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereUuid($value)
 */
	class Role extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $uuid
 * @property int $maintenance_id
 * @property string $nomor_spk
 * @property string|null $tanggal_mulai_efektif
 * @property string|null $tanggal_selesai_target
 * @property numeric|null $pagu_anggaran_disetujui
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApprovalLog> $approvalLogs
 * @property-read int|null $approval_logs_count
 * @property-read \App\Models\MaintenanceRequest $maintenance
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereMaintenanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereNomorSpk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK wherePaguAnggaranDisetujui($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereTanggalMulaiEfektif($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereTanggalSelesaiTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SPK whereUuid($value)
 */
	class SPK extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $uuid
 * @property int $role_id
 * @property string $email
 * @property string $username
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property bool $is_active
 * @property int $force_password_change
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Role $role
 * @property-read \App\Models\UserProfile|null $userProfile
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereForcePasswordChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUuid($value)
 */
	class User extends \Eloquent implements \Tymon\JWTAuth\Contracts\JWTSubject {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $fullname
 * @property string|null $phone
 * @property string|null $location
 * @property string|null $avatar_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProfile whereUuid($value)
 */
	class UserProfile extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $jti
 * @property string|null $device_info
 * @property string $last_activity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession whereDeviceInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession whereJti($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession whereLastActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserSession whereUserId($value)
 */
	class UserSession extends \Eloquent {}
}

