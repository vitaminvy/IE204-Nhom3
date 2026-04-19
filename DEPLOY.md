# Deploy Len Hosting Bang GitHub Actions

Tai lieu nay dung cho cach:

- sua code hoac file truyen tren local
- `git push` len GitHub
- GitHub Actions tu upload theme len host qua FTP
- truyen se duoc import tay trong WordPress admin khi ban muon

## 1. Dua project len GitHub

Neu chua co repo GitHub:

1. Tao 1 repository moi tren GitHub.
2. Push folder `comeout-with-me` len repo do.

## 2. Tao FTP account tren hosting

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

## 3. Them GitHub Secrets

Trong GitHub repo:

`Settings -> Secrets and variables -> Actions`

Tao cac secret sau:

- `FTP_SERVER`
- `FTP_USERNAME`
- `FTP_PASSWORD`
- `FTP_SERVER_DIR`

## 4. Workflow da co san

Repo da co file:

`/.github/workflows/deploy-theme.yml`

Workflow nay se:

1. checkout code
2. upload theme len host qua FTP

No se chay khi:

- push len nhanh `main`
- push len nhanh `master`
- hoac bam tay trong tab `Actions`

## 5. Khi nao giao dien va truyen se cap nhat

Sau khi setup xong:

1. Ban sua file trong `import/` hoac file theme tren local.
2. Ban commit va `git push`.
3. GitHub Actions upload len host.
4. Giao dien va code theme tren site that duoc cap nhat.
5. Neu ban sua file truyen trong `import/`, file txt cung se len host nhung chua tu import vao database.
6. Khi can, vao `Tools -> Import truyện` tren host de import tay.

## 6. Cach kiem tra nhanh

Sau 1 lan push:

- vao `GitHub -> Actions` xem workflow xanh hay do
- neu xanh, mo site that kiem tra giao dien moi
- neu do, xem log buoc `Upload theme via FTP`

## 7. Luu y quan trong

- Cách nay khong phu hop neu ban muon "save phat len host ngay" ma khong push git.
- Day la deploy theo dot: sua -> commit -> push -> host cap nhat giao dien.
- Folder `import/` se duoc upload cung theme, nen truyen txt cua ban cung duoc dua len host.
- File truyen se chi len giao dien sau khi ban tu import trong WordPress admin.
- Neu ban khong muon day len host 1 so file local-only, co the them vao `exclude` trong workflow.
