import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';

const apiUrl = '/api/v1';

function App() {
    const [token, setToken] = useState(localStorage.getItem('api_token'));
    const [user, setUser] = useState(null);
    const [products, setProducts] = useState([]);
    const [productMeta, setProductMeta] = useState({ current_page: 1, last_page: 1 });
    const [posts, setPosts] = useState([]);
    const [section, setSection] = useState('products');
    const [error, setError] = useState('');
    const [notice, setNotice] = useState('');
    const [credentials, setCredentials] = useState({ email: '', password: '', device_name: 'React Dashboard' });
    const [product, setProduct] = useState({ name: '', price: '', description: '', stock: '', image: null });
    const [filters, setFilters] = useState({ search: '', min_price: '', max_price: '', sort_by: 'id', sort_direction: 'desc', page: 1 });
    const [post, setPost] = useState({ title: '', body: '' });

    const request = async (path, options = {}) => {
        const response = await fetch(`${apiUrl}${path}`, {
            ...options,
            headers: {
                Accept: 'application/json',
                ...(token ? { Authorization: `Bearer ${token}` } : {}),
                ...(options.body && !(options.body instanceof FormData) ? { 'Content-Type': 'application/json' } : {}),
                ...options.headers,
            },
        });
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(payload.message || 'Request failed.');
        return payload;
    };

    const loadData = async () => {
        try {
            const [profile, productResponse, postResponse] = await Promise.all([
                request('/user'), request(`/products?${new URLSearchParams(filters)}`), request('/posts'),
            ]);
            setUser(profile.data);
            setProducts(productResponse.data);
            setProductMeta(productResponse.meta);
            setPosts(postResponse.data);
            setError('');
        } catch (e) { setError(e.message); }
    };

    useEffect(() => { if (token) loadData(); }, [token, filters]);

    const login = async (event) => {
        event.preventDefault();
        try {
            const result = await request('/login', { method: 'POST', body: JSON.stringify(credentials) });
            localStorage.setItem('api_token', result.data.token);
            setToken(result.data.token);
            setNotice('Logged in successfully.');
        } catch (e) { setError(e.message); }
    };

    const logout = async () => {
        try { await request('/logout', { method: 'POST' }); } catch (_) {}
        localStorage.removeItem('api_token'); setToken(null); setUser(null); setProducts([]); setPosts([]);
    };

    const createItem = async (event, type) => {
        event.preventDefault();
        const data = type === 'products' ? product : post;
        try {
            const body = type === 'products' ? new FormData() : JSON.stringify(data);
            if (type === 'products') Object.entries(data).forEach(([key, value]) => { if (value !== null && value !== '') body.append(key, value); });
            await request(`/${type}`, { method: 'POST', body });
            setNotice(`${type.slice(0, -1)} created successfully.`);
            if (type === 'products') setProduct({ name: '', price: '', description: '', stock: '', image: null });
            else setPost({ title: '', body: '' });
            loadData();
        } catch (e) { setError(e.message); }
    };

    const removeItem = async (type, id) => {
        if (!window.confirm('Delete this item?')) return;
        try { await request(`/${type}/${id}`, { method: 'DELETE' }); setNotice('Deleted successfully.'); loadData(); }
        catch (e) { setError(e.message); }
    };

    const editItem = async (type, item) => {
        const primaryKey = type === 'products' ? 'name' : 'title';
        const primary = window.prompt(primaryKey === 'name' ? 'Product name' : 'Post title', item[primaryKey]);
        if (primary === null) return;
        const content = window.prompt(type === 'products' ? 'Description' : 'Body', item.description || item.body || '');
        if (content === null) return;
        const payload = type === 'products'
            ? { name: primary, description: content, price: window.prompt('Price', item.price) || item.price, stock: item.stock || 0 }
            : { title: primary, body: content, published_at: item.published_at };
        try { await request(`/${type}/${item.id}`, { method: 'PUT', body: JSON.stringify(payload) }); setNotice('Updated successfully.'); loadData(); }
        catch (e) { setError(e.message); }
    };

    if (!token) return <main className="mx-auto mt-24 max-w-md rounded-xl bg-white p-8 shadow"><h1 className="mb-6 text-2xl font-bold">API Dashboard</h1><form onSubmit={login} className="space-y-4"><input className="w-full rounded border p-3" type="email" placeholder="Email" value={credentials.email} onChange={e => setCredentials({ ...credentials, email: e.target.value })} required /><input className="w-full rounded border p-3" type="password" placeholder="Password" value={credentials.password} onChange={e => setCredentials({ ...credentials, password: e.target.value })} required /><button className="w-full rounded bg-indigo-600 p-3 font-semibold text-white">Login</button></form>{error && <p className="mt-4 text-red-600">{error}</p>}</main>;

    const isAdmin = user?.role === 'admin';
    const items = section === 'products' ? products : posts;
    return <main className="mx-auto max-w-6xl p-6"><header className="mb-8 flex items-center justify-between"><div><h1 className="text-3xl font-bold">CRUD Dashboard</h1><p className="text-slate-600">{user?.name} · {user?.role}</p></div><button onClick={logout} className="rounded bg-slate-800 px-4 py-2 text-white">Logout</button></header>{!user?.email_verified_at && <div className="mb-4 rounded bg-amber-100 p-4">Verify your email to use posts and products.</div>}{error && <p className="mb-4 rounded bg-red-100 p-3 text-red-700">{error}</p>}{notice && <p className="mb-4 rounded bg-green-100 p-3 text-green-700">{notice}</p>}<nav className="mb-6 flex gap-3"><button onClick={() => setSection('products')} className="rounded bg-white px-4 py-2 shadow">Products</button><button onClick={() => setSection('posts')} className="rounded bg-white px-4 py-2 shadow">Posts</button></nav>{section === 'products' && <div className="mb-6 grid gap-3 rounded bg-white p-4 shadow md:grid-cols-4"><input className="rounded border p-2" placeholder="Search products" value={filters.search} onChange={e => setFilters({ ...filters, search: e.target.value, page: 1 })} /><input className="rounded border p-2" placeholder="Min price" type="number" value={filters.min_price} onChange={e => setFilters({ ...filters, min_price: e.target.value, page: 1 })} /><input className="rounded border p-2" placeholder="Max price" type="number" value={filters.max_price} onChange={e => setFilters({ ...filters, max_price: e.target.value, page: 1 })} /><button onClick={loadData} className="rounded bg-slate-700 p-2 text-white">Apply filters</button></div>}{isAdmin && <form onSubmit={e => createItem(e, section)} className="mb-6 grid gap-3 rounded bg-white p-5 shadow">{section === 'products' ? <><input className="rounded border p-2" placeholder="Name" value={product.name} onChange={e => setProduct({ ...product, name: e.target.value })} required /><input className="rounded border p-2" placeholder="Price" type="number" step="0.01" value={product.price} onChange={e => setProduct({ ...product, price: e.target.value })} required /><textarea className="rounded border p-2" placeholder="Description" value={product.description} onChange={e => setProduct({ ...product, description: e.target.value })} /><input type="file" accept="image/*" onChange={e => setProduct({ ...product, image: e.target.files[0] || null })} /></> : <><input className="rounded border p-2" placeholder="Title" value={post.title} onChange={e => setPost({ ...post, title: e.target.value })} required /><textarea className="rounded border p-2" placeholder="Body" value={post.body} onChange={e => setPost({ ...post, body: e.target.value })} required /></>}<button className="rounded bg-indigo-600 p-2 text-white">Create {section.slice(0, -1)}</button></form>}<section className="grid gap-4 md:grid-cols-2">{items.map(item => <article key={item.id} className="rounded bg-white p-5 shadow">{item.image_url && <img className="mb-3 h-40 w-full rounded object-cover" src={item.image_url} alt={item.name} />}<h2 className="text-xl font-semibold">{item.name || item.title}</h2>{item.price && <p className="mt-1 font-medium">₹{item.price}</p>}<p className="mt-2 text-slate-600">{item.description || item.body}</p>{section === 'posts' && <p className="mt-3 text-sm text-slate-500">♥ {item.likes_count} · 💬 {item.comments_count}</p>}{isAdmin && <div className="mt-4 flex gap-2"><button onClick={() => editItem(section, item)} className="rounded bg-amber-500 px-3 py-1 text-white">Edit</button><button onClick={() => removeItem(section, item.id)} className="rounded bg-red-600 px-3 py-1 text-white">Delete</button></div>}</article>)}</section>{section === 'products' && <div className="mt-6 flex justify-center gap-3"><button disabled={productMeta.current_page <= 1} onClick={() => setFilters({ ...filters, page: filters.page - 1 })} className="rounded bg-white px-4 py-2 shadow disabled:opacity-50">Previous</button><span className="py-2">Page {productMeta.current_page} of {productMeta.last_page}</span><button disabled={productMeta.current_page >= productMeta.last_page} onClick={() => setFilters({ ...filters, page: filters.page + 1 })} className="rounded bg-white px-4 py-2 shadow disabled:opacity-50">Next</button></div>}</main>;
}

createRoot(document.getElementById('app')).render(<App />);
