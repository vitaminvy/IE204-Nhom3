# Deploy Len Hosting Bang GitHub Actions

Tai lieu nay dung cho cach:

- sua code hoac file truyen tren local
- `git push` len GitHub
- GitHub Actions tu upload theme len host qua FTP
- host tu goi importer de cap nhat truyen

## 1. Dua project len GitHub

Neu chua co repo GitHub:

1. Tao 1 repository moi tren GitHub.
2. Push folder `comeout-with-me` len repo do.

## 2. Cau hinh token tren host

Theme da co san remote sync endpoint:

```text
/wp-admin/admin-post.php?action=cowm_story_remote_sync&token=YOUR_SECRET
```

Ban can them 1 dong vao `wp-config.php` tren host:

```php
define('COWM_REMOTE_SYNC_TOKEN', 'mot-chuoi-bi-mat-rat-dai');
```

Vi du URL day du:

```text
https://domaincuaban.com/wp-admin/admin-post.php?action=cowm_story_remote_sync&token=mot-chuoi-bi-mat-rat-dai
```

## 3. Tao FTP account tren hosting

Trong panel host cua ban da co muc `Tài khoản FTP`.

Ban can 4 thong tin:

- FTP host
- FTP username
- FTP password
- remote folder cua theme

Thuong remote folder se giong 1 trong cac mau:

```text
public_html/wp-content/themes/comeout-with-me/
domains/domaincuaban.com/public_html/wp-content/themes/comeout-with-me/
```

Neu WordPress dang nam trong subfolder, doi duong dan cho dung.

## 4. Them GitHub Secrets

Trong GitHub repo:

`Settings -> Secrets and variables -> Actions`

Tao cac secret sau:

- `FTP_SERVER`
- `FTP_USERNAME`
- `FTP_PASSWORD`
- `FTP_SERVER_DIR`
- `REMOTE_SYNC_URL`

Gia tri `REMOTE_SYNC_URL` la URL day du da kem token, vi du:

```text
https://domaincuaban.com/wp-admin/admin-post.php?action=cowm_story_remote_sync&token=mot-chuoi-bi-mat-rat-dai
```

## 5. Workflow da co san

Repo da co file:

`/.github/workflows/deploy-theme.yml`

Workflow nay se:

1. checkout code
2. upload theme len host qua FTP
3. goi remote sync URL de host tu import lai truyen

No se chay khi:

- push len nhanh `main`
- push len nhanh `master`
- hoac bam tay trong tab `Actions`

## 6. Khi nao truyen se tu cap nhat

Sau khi setup xong:

1. Ban sua file trong `import/` hoac file theme tren local.
2. Ban commit va `git push`.
3. GitHub Actions upload len host.
4. Host tu chay importer.
5. Truyen moi hoac noi dung moi se len site that.

## 7. Cach kiem tra nhanh

Sau 1 lan push:

- vao `GitHub -> Actions` xem workflow xanh hay do
- neu xanh, mo site that kiem tra truyện/chương moi
- neu do, xem log buoc `Upload theme via FTP` hoac `Trigger remote story sync`

## 8. Luu y quan trong

- Cách nay khong phu hop neu ban muon "save phat len host ngay" ma khong push git.
- Day la deploy theo dot: sua -> commit -> push -> host cap nhat.
- Folder `import/` se duoc upload cung theme, nen truyen txt cua ban cung duoc dua len host.
- Neu ban khong muon day len host 1 so file local-only, co the them vao `exclude` trong workflow.
