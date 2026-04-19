# Come Out With Me WordPress Theme

Theme scaffold WordPress classic, co `front-page.php`, `theme.json`, menu WordPress, Customizer va homepage query dong cho:

- `Truyen moi cap nhat`
- `Tra da via he / Blog / Review`

## Cac file chinh

- `front-page.php`: template trang chu
- `header.php`, `footer.php`: layout chung
- `functions.php`: theme supports, menus, enqueue assets
- `inc/customizer.php`: noi khai bao text/link/source cho homepage trong admin
- `inc/meta-boxes.php`: them field badge/progress cho bai viet
- `template-parts/home/*.php`: tach section nho de de maintain
- `assets/css/main.css`: giao dien frontend
- `theme.json`: block/editor compatibility

## Cach dung

1. Copy thu muc `comeout-with-me` vao `wp-content/themes/`.
2. Kich hoat theme trong WordPress admin.
3. Vao `Appearance > Menus` gan menu cho `Primary Menu` va `Footer Menu`.
4. Vao `Appearance > Customize > Come Out With Me Homepage` de sua hero, card, title, link va chon category/post type cho homepage.
5. Tao bai viet co `Featured Image`, `Excerpt` va category phu hop cho:
   - section truyen moi
   - section blog/review

## Xem live local bang Docker

Neu ban muon preview theme ngay tren may ma khong can cai PHP/WordPress thu cong, repo da co san `docker-compose.yml`.

1. Mo terminal tai thu muc `comeout-with-me`.
2. Chay:

   ```bash
   docker compose up -d
   ```

3. Mo `http://localhost:8080`.
4. Hoan tat man hinh cai dat WordPress lan dau.
5. Vao `Appearance > Themes` va kich hoat theme `Come Out With Me`.

Sau do, moi khi sua file trong repo nay, ban chi can refresh trinh duyet la se thay thay doi frontend gan nhu ngay lap tuc vi theme dang duoc mount truc tiep vao container.

Lenh huu ich:

- Dung moi truong local:

  ```bash
  docker compose down
  ```

- Xoa ca database va du lieu WordPress local de lam lai tu dau:

  ```bash
  docker compose down -v
  ```

Neu cong `8080` dang bi trung, sua dong `8080:80` trong `docker-compose.yml` thanh vi du `8081:80`, roi mo `http://localhost:8081`.

## Du lieu dong cho "Truyen moi cap nhat"

Mac dinh section nay query bai viet moi nhat theo:

- `Source Post Type`
- `Source Category`

Neu muon hien thi badge va meta giong mockup, trong man hinh edit bai viet se co meta box:

- `Status badge`: vi du `Dang ra`, `Hoan thanh`
- `Secondary badge`: vi du `My Cuong`, `Co Trang`
- `Progress label`: vi du `Chuong 45`, `END`

Neu bo trong, theme se tu fallback sang category/tag va thoi gian cap nhat.

## Du lieu dong cho "Blog / Review"

Section blog query bai viet moi nhat tu category ban chon trong Customizer. Neu khong chon category, no se lay tu post mac dinh.

## Deploy len hosting

Neu ban muon theo kieu `git push -> host tu upload va tu import truyen`, repo da co san workflow:

- `/.github/workflows/deploy-theme.yml`

Huong dan cau hinh chi tiet nam trong:

- `DEPLOY.md`

## Mo rong SEO

- Heading hierarchy duoc giu theo `H1 > H2 > H3`
- Anh dung `Featured Image` + alt text tu WordPress media
- `title-tag`, `custom-logo`, `post-thumbnails`, `align-wide`, `editor-styles` da bat san
- Co the ket hop them voi Yoast SEO / Rank Math ma khong can sua template
