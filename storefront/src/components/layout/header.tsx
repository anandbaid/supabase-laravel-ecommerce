import Image from "next/image";
import Link from "next/link";
import { getCategoryTree } from "@/lib/data";
import { adminPanelUrl, siteName } from "@/lib/config";
import { AccountMenu, CartLink, WishlistLink } from "./header-actions";
import { CategoriesMenu } from "./categories-menu";
import { MainNav, MobileNav } from "./main-nav";
import { SearchBox } from "./search-box";

export async function Header() {
  const categories = await getCategoryTree();

  return (
    <header className="sticky top-0 z-50 bg-white shadow-sm">
      <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3">
        <div className="flex items-center gap-2">
          <MobileNav categories={categories} />
          <Link href="/" className="flex items-center gap-2" aria-label={`${siteName} home`}>
            <Image src="/images/logo.png" alt={siteName} width={160} height={40} className="h-10 w-auto" priority />
          </Link>
        </div>

        <MainNav>
          <CategoriesMenu categories={categories} />
        </MainNav>

        <SearchBox />

        <div className="flex items-center gap-4">
          <AccountMenu adminPanelUrl={adminPanelUrl} />
          <WishlistLink />
          <CartLink />
        </div>
      </div>
    </header>
  );
}
