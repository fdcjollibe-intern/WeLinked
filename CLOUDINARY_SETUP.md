# WeLinked - Cloudinary Integration & Settings Update

## 📋 What's Been Added

### 1. Profile Photo & Gender Fields in Account Settings
- ✅ Profile photo upload with preview
- ✅ Gender selection (Male, Female, Prefer not to say)
- ✅ Default gender set to "Prefer not to say" for new registrations
- ✅ Database schema updated with `gender` column

### 2. Cloudinary CDN Integration
- ✅ Cloudinary PHP SDK integrated for media uploads
- ✅ Profile photos organized in `/profilephotos` folder
- ✅ Posts media will be organized in `/posts` folder
- ✅ Automatic optimization (quality: auto, format: auto)
- ✅ Smart transformations and responsive delivery

### 3. File Organization
- **Profile Photos**: `profilephotos/user_{userId}_{hash}`
- **Post Media**: `posts/post_{postId}_{hash}` or `posts/user_{userId}_{hash}`
- All media URLs stored in MySQL

---

## 🚀 Setup Instructions

### Step 1: Install Cloudinary PHP SDK

Navigate to your backend directory and run:

```bash
cd backend
composer require cloudinary/cloudinary_php
```

### Step 2: Configure Cloudinary Credentials

**Option A: Using Environment Variables (Recommended for Production)**

Create or edit `.env` file in your backend root:

```bash
# backend/.env
CLOUDINARY_CLOUD_NAME=dn6rffrwk
CLOUDINARY_API_KEY=YOUR_API_KEY_HERE
CLOUDINARY_API_SECRET=YOUR_API_SECRET_HERE
```

**Option B: Direct Configuration (For Development)**

Edit `backend/config/cloudinary.php` (lines 12-14):

```php
'cloud_name' => env('CLOUDINARY_CLOUD_NAME', 'dn6rffrwk'),
'api_key' => env('CLOUDINARY_API_KEY', 'YOUR_API_KEY_HERE'),
'api_secret' => env('CLOUDINARY_API_SECRET', 'YOUR_API_SECRET_HERE'),
```

**📌 Where to Get Your Cloudinary Credentials:**
- Log in to [Cloudinary Dashboard](https://cloudinary.com/console)
- Copy your **API Key** and **API Secret**
- Cloud Name is already set: `dn6rffrwk`

### Step 3: Apply Database Migration

Run this SQL migration to add the `gender` column:

```bash
mysql -u your_username -p welinked_db < db/20260213_add_gender_column.sql
```

**Or manually run:**

```sql
USE welinked_db;

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS gender ENUM('Male', 'Female', 'Prefer not to say') 
NOT NULL DEFAULT 'Prefer not to say'
AFTER profile_photo_path;

CREATE INDEX IF NOT EXISTS idx_users_gender ON users(gender);
```

**✅ This migration is safe:**
- Uses `IF NOT EXISTS` to avoid conflicts
- Sets default value for existing records
- Non-destructive (won't break existing data)

### Step 4: Verify Setup

1. **Check Composer Installation:**
   ```bash
   cd backend
   composer show cloudinary/cloudinary_php
   ```

2. **Test Profile Photo Upload:**
   - Navigate to Settings → Account Information
   - Click "Choose Photo" and upload an image
   - Should see success message and preview

3. **Check Database:**
   ```sql
   USE welinked_db;
   DESCRIBE users;
   -- You should see the 'gender' column
   ```

---

## 📁 File Structure Changes

```
backend/
├── config/
│   ├── cloudinary.php            # NEW: Cloudinary configuration
│   └── routes.php                 # UPDATED: Added upload routes
├── src/
│   ├── Controller/
│   │   ├── RegisterController.php     # UPDATED: Default gender
│   │   └── SettingsController.php     # UPDATED: Profile photo & gender
│   └── Service/
│       └── CloudinaryUploader.php     # NEW: Upload service
└── templates/
    └── Settings/
        └── index.php                   # UPDATED: UI for photo & gender

db/
├── init-db.sql                        # UPDATED: Added gender column
└── 20260213_add_gender_column.sql    # NEW: Migration file
```

---

## 🔐 Security Notes

1. **Never commit API secrets to git:**
   - Add `.env` to `.gitignore`
   - Use environment variables in production

2. **File Upload Validation:**
   - Max size: 10MB for profile photos
   - Allowed types: JPG, PNG, GIF, WEBP
   - Server-side validation implemented

3. **Cloudinary Security:**
   - All uploads use HTTPS
   - Public IDs are hashed for privacy
   - Transformations applied server-side

---

## 🎨 Usage Examples

### Upload Profile Photo (User-Facing)
1. Go to Settings → Account Information
2. Click "Choose Photo"
3. Select an image (max 10MB)
4. Preview appears instantly
5. Photo uploads to Cloudinary automatically

### Change Gender
1. Go to Settings → Account Information
2. Select from dropdown: Male, Female, or Prefer not to say
3. Click "Save Changes"

---

## 📦 Next Steps for Post Media Integration

To integrate Cloudinary for post uploads (images/videos):

1. **Update DashboardUploadsController:**
   ```php
   use App\Service\CloudinaryUploader;
   
   public function upload() {
       $uploader = new CloudinaryUploader();
       $result = $uploader->uploadPostImage($filePath, $userId, $postId);
       // Store $result['url'] in database
   }
   ```

2. **For Videos/Reels:**
   ```php
   $result = $uploader->uploadPostVideo($filePath, $userId, $postId);
   ```

3. **Database Storage:**
   - Store Cloudinary URLs in `post_attachments.file_path`
   - Keep `file_type` as 'image' or 'video'
   - Original local storage can be removed

---

## 🐛 Troubleshooting

### "Cloudinary class not found"
```bash
cd backend
composer dump-autoload
composer require cloudinary/cloudinary_php
```

### "Upload failed" error
1. Check API credentials in `cloudinary.php`
2. Verify temp directory is writable: `backend/tmp/uploads/`
3. Check error logs: `backend/logs/error.log`

### Migration already applied
- If column exists, the migration will skip it (`IF NOT EXISTS`)
- Safe to run multiple times

### Profile photo not displaying
1. Check if URL is stored in database:
   ```sql
   SELECT profile_photo_path FROM users WHERE id = YOUR_ID;
   ```
2. Verify Cloudinary URL is accessible (HTTPS)

---

## 📊 Database Schema Reference

```sql
users Table:
- id                    BIGINT UNSIGNED PRIMARY KEY
- full_name             VARCHAR(150)
- username              VARCHAR(50) UNIQUE
- email                 VARCHAR(100) UNIQUE
- password_hash         VARCHAR(255)
- profile_photo_path    VARCHAR(255) NULL           -- Cloudinary URL
- gender                ENUM('Male', 'Female', 'Prefer not to say') DEFAULT 'Prefer not to say'
- theme_preference      ENUM('system', 'light', 'dark') DEFAULT 'system'
- created_at            DATETIME
- updated_at            DATETIME
```

---

## ✅ Testing Checklist

- [ ] Composer dependencies installed
- [ ] Cloudinary credentials configured
- [ ] Database migration applied
- [ ] Profile photo upload works
- [ ] Gender selection saves correctly
- [ ] New registrations default to "Prefer not to say"
- [ ] Profile photo URLs stored in database
- [ ] Settings page loads without errors

---

## 📞 Support

If you encounter issues:
1. Check `backend/logs/error.log`
2. Verify database connection
3. Test Cloudinary credentials in dashboard
4. Ensure temp directory permissions: `chmod -R 755 backend/tmp/uploads`

---

**Implementation Date:** February 13, 2026
**Status:** ✅ Ready for production after completing setup steps above
